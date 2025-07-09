<template>
  <VideoPlayer
    class="video-js vjs-default-skin"
    src="/your-path/video.mp4"
    poster="/your-path/poster.jpg"
    controls
    :loop="true"
    :volume="0.6"
    @mounted="handleMounted"
    @ready="handleReady"
    @play="handlePlay"
    @pause="handlePause"
    @ended="handleEnded"
    @seeking="handleSeeking"
  />
</template>

<script setup>
import { VideoPlayer } from '@videojs-player/vue'
import 'video.js/dist/video-js.css'

// Métodos para manejar los eventos
const handleMounted = (player) => {
  console.log('Mounted:', player)
}

const handleReady = (player) => {
  console.log('Player is ready:', player)
}

const handlePlay = () => {
  console.log('Video started playing')
}

const handlePause = () => {
  console.log('Video paused')
}

const handleEnded = () => {
  console.log('Video ended')
}

const handleSeeking = () => {
  console.log('Seeking in the video')
}
</script>
