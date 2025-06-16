// composables/useToast.js
import { createVNode, render } from 'vue';
import ToastMessage from '@/Components/ToastMessage.vue';

export function useToast() {
  return function showToast(message, type = 'success', duration = 3000) {
    const container = document.createElement('div');
    document.body.appendChild(container);

    const vnode = createVNode(ToastMessage, { message, type, duration });
    render(vnode, container);

    setTimeout(() => {
      render(null, container);
      document.body.removeChild(container);
    }, duration + 500);
  };
}
