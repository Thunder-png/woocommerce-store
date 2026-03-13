(function () {
  const faqButtons = document.querySelectorAll('.wcs-blog-faq__question');

  faqButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const expanded = btn.getAttribute('aria-expanded') === 'true';
      const container = btn.closest('.wcs-blog-faq__item');
      if (!container) return;

      const answer = container.querySelector('.wcs-blog-faq__answer');
      if (!answer) return;

      btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      answer.hidden = expanded;
    });
  });
})();*** End Patch```}>>();
{"cursor": 0, "id": "functions.ApplyPatch"}>assistant to=functions.ApplyPatch мунасistantიცინო to=functions.ApplyPatch ***!
