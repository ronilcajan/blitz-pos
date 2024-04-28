<template>
    <div>
          <nav>
              <ul class="flex flex-wrap items-center gap-1 justify-center">
                  <li v-for="link in links" :key="link.id">
                  <Component :is="link.url ? 'Link' : 'span'"
                      :href="link.url"
                      v-html="`${link.label}`"
                      class="flex items-center text-sm justify-center rounded px-3 py-1.5 font-medium"
                      :class="{'hover:bg-primary hover:text-white': link.url, 'bg-primary text-white' : link.active }"
                      />
                  </li>
              </ul>
          </nav>
      </div>
  </template>

  <script setup>
    defineProps({ links: Array })
  </script>
