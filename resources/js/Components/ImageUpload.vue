<script setup>
import { ref, watch } from 'vue';

const props = defineProps(
    {
        image: {
            type: String,
        }
    }
);

const model = defineModel({
    type: Object,
    required: true,
});
const image_preview = ref(props.image);

watch(() => model.file, (newVal) => {
    image_preview.value = newVal;
    model.avatar = '';
});

const removeImage = () => {
    image_preview.value = null;
}

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (!file.type.startsWith('image/')) {
        alert('Please select an image file');
        return;
    }
    image_preview.value = URL.createObjectURL(file);
}

</script>

<template>
    <div class="flex items-center justify-center w-full relative">
        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full border-2 border-gray-300 border-dashed rounded-lg">
            <a href="#" class="bg-red-500 text-white rounded-full p-1 hover:bg-red-700 absolute top-2 right-2 transform translate-x-1/2 -translate-y-1/2 z-20" @click.prevent="removeImage" v-if="image_preview">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </a>
            <div v-if="!image_preview" class="flex flex-col items-center justify-center pt-5 pb-6">
                <svg  xmlns="http://www.w3.org/2000/svg"  width="200"  height="200"  viewBox="0 0 24 24"  fill="none"  stroke="#9b9797"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-package"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /><path d="M16 5.25l-8 4.5" /></svg>
            </div>
            <div id="preview" >
                <img class="hover:opacity-90" v-if="image_preview" :src="image_preview" />
            </div>
        </label>
    </div>
    <div class="mt-3">
        <input accept="image/*" @input="model.avatar = $event.target.files[0]" type="file" class="file-input file-input-bordered file-input-sm w-full" @change="onFileChange"/>
        <progress v-if="model.progress" :value="model.progress.percentage" class="progress" max="100">
            {{ model.progress.percentage }}%
        </progress>
        <InputError class="mt-2" :message="model.errors.avatar" />
    </div>
</template>




