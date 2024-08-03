<script setup>
import { ref, computed } from 'vue';
import { QrcodeStream } from 'vue-qrcode-reader'

const result = ref('')
const error = ref('')
const scanSound = ref(null);
const selectedConstraints = ref({ facingMode: 'environment' })
const base_url = import.meta.env.VITE_APP_URL;

const emit = defineEmits(['scanned-barcode']);

function onDetect(detectedCodes) {
    console.log(detectedCodes)
    result.value = JSON.stringify(detectedCodes[0].rawValue)
                .replace(/^\"/, '')  // Remove [" from the start
                .replace(/\"$/, '')  // Remove "] from the end

    onScanDetected();   
    emit('scanned-barcode', result.value); // Emit the event with the detected barcode value
 
}

/*** select camera ***/
const defaultConstraintOptions = [
    { label: 'rear camera', constraints: { facingMode: 'environment' } },
    { label: 'front camera', constraints: { facingMode: 'user' } }
]
const constraintOptions = ref(defaultConstraintOptions)

async function onCameraReady() {
    // NOTE: on iOS we can't invoke `enumerateDevices` before the user has given
    // camera access permission. `QrcodeStream` internally takes care of
    // requesting the permissions. The `camera-on` event should guarantee that this
    // has happened.
    const devices = await navigator.mediaDevices.enumerateDevices()
    const videoDevices = devices.filter(({ kind }) => kind === 'videoinput')

  constraintOptions.value = [
        ...defaultConstraintOptions,
        ...videoDevices.map(({ deviceId, label }) => ({
        label: `${label} (ID: ${deviceId})`,
        constraints: { deviceId }
        }))
    ]

    error.value = ''
}

function paintCenterText(detectedCodes, ctx) {
    for (const detectedCode of detectedCodes) {
        const { boundingBox, rawValue } = detectedCode

        const centerX = boundingBox.x + boundingBox.width / 2
        const centerY = boundingBox.y + boundingBox.height / 2

        const fontSize = Math.max(12, (50 * boundingBox.width) / ctx.canvas.width)

        ctx.font = `bold ${fontSize}px sans-serif`
        ctx.textAlign = 'center'

        ctx.lineWidth = 3
        ctx.strokeStyle = '#35495e'
        ctx.strokeText(detectedCode.rawValue, centerX, centerY)

        ctx.fillStyle = '#5cb984'
        ctx.fillText(rawValue, centerX, centerY)
    }
}

function paintOutline(detectedCodes, ctx) {
    for (const detectedCode of detectedCodes) {
        const [firstPoint, ...otherPoints] = detectedCode.cornerPoints

        ctx.strokeStyle = 'red'

        ctx.beginPath()
        ctx.moveTo(firstPoint.x, firstPoint.y)
        for (const { x, y } of otherPoints) {
        ctx.lineTo(x, y)
        }
        ctx.lineTo(firstPoint.x, firstPoint.y)
        ctx.closePath()
        ctx.stroke()
    }
}
function paintBoundingBox(detectedCodes, ctx) {
    for (const detectedCode of detectedCodes) {
        const {
        boundingBox: { x, y, width, height }
        } = detectedCode

        ctx.lineWidth = 2
        ctx.strokeStyle = '#007bff'
        ctx.strokeRect(x, y, width, height)
    }
}

const trackFunctionOptions = [
    { text: 'nothing (default)', value: undefined },
    { text: 'outline', value: paintOutline },
    { text: 'centered text', value: paintCenterText },
    { text: 'bounding box', value: paintBoundingBox }
]
const trackFunctionSelected = ref(trackFunctionOptions[3])

const barcodeFormats = ref({
    aztec: false,
    code_128: false,
    code_39: false,
    code_93: false,
    codabar: false,
    databar: false,
    databar_expanded: false,
    data_matrix: false,
    dx_film_edge: false,
    ean_13: true,
    ean_8: true,
    itf: false,
    maxi_code: false,
    micro_qr_code: false,
    pdf417: false,
    qr_code: true,
    rm_qr_code: false,
    upc_a: false,
    upc_e: false,
    linear_codes: false,
    matrix_codes: false
})
const selectedBarcodeFormats = computed(() => {
  return Object.keys(barcodeFormats.value).filter((format) => barcodeFormats.value[format])
})

/*** error handling ***/


function onError(err) {
    error.value = `[${err.name}]: `

    if (err.name === 'NotAllowedError') {
        error.value += 'you need to grant camera access permission'
    } else if (err.name === 'NotFoundError') {
        error.value += 'no camera on this device'
    } else if (err.name === 'NotSupportedError') {
        error.value += 'secure context required (HTTPS, localhost)'
    } else if (err.name === 'NotReadableError') {
        error.value += 'is the camera already in use?'
    } else if (err.name === 'OverconstrainedError') {
        error.value += 'installed cameras are not suitable'
    } else if (err.name === 'StreamApiNotSupportedError') {
        error.value += 'Stream API is not supported in this browser'
    } else if (err.name === 'InsecureContextError') {
        error.value +=
        'Camera access is only permitted in secure context. Use HTTPS or localhost rather than HTTP.'
    } else {
        error.value += err.message
    }
}


const playSound = () => {
      if (scanSound.value && typeof scanSound.value.play === 'function') {
        scanSound.value.play().catch(error => {
          console.error('Failed to play sound:', error);
        });
      } else {
        console.error('Audio element not found or play is not a function');
      }
    };

    // Example: Call this function when a scan is detected
const onScanDetected = () => {
    playSound();
};


</script>

<template>
    <div>
        <audio ref="scanSound" :src="`${base_url}/beep.mp3`"></audio>
        <div class="flex justify-center flex-col border border-dashed border-primary p-2 rounded-lg">
            <QrcodeStream v-if="!error" height="300       "
                :constraints="selectedConstraints"
                :track="trackFunctionSelected.value"
                :formats="selectedBarcodeFormats"
                @error="onError"
                @detect="onDetect"
                @camera-on="onCameraReady"
            />
        </div>
        <p class="error" v-if="error">{{ error }}</p>
        <select class="select select-sm mt-3 select-bordered w-full max-w-xs" v-model="selectedConstraints">
            <option
            v-for="option in constraintOptions"
            :key="option.label"
            :value="option.constraints"
            >
            {{ option.label }}
            </option>
        </select>
        <TextInput class="w-full mt-6" v-model="result" placeholder="Scanned barcode" readonly/>
    </div>
    
 
    
</template>