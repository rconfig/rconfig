import { reactive } from 'vue'


export default function useScrollToBottom(viewstate, modelName, modelObject) {
    function scrollToBottom() {
        let scrollingElement = document.querySelector('.pf-c-page__main');
        scrollingElement.scrollTop = scrollingElement.scrollHeight;
    }
  
    return {
        scrollToBottom
    };
  };
  