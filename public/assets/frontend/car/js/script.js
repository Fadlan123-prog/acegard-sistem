$(function () {
  'use strict';

  /*--------------------------------------------------------------
    GLightbox Preview
  --------------------------------------------------------------*/
  // Initialize GLightbox
  const lightbox = GLightbox({
    selector: '.glightbox'
  });

  /*--------------------------------------------------------------
    AJAX Submit Form
  --------------------------------------------------------------*/
  // Take all the forms we want to apply Bootstrap custom validation styles to
  const forms = $('.needs-validation');

  forms.on('submit', function (event) {
    const form = $(this);
    const actionInput = form.find("input[name='action']");

    if (!form[0].checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
    } else {
      event.preventDefault();

      $('.submit_form').html('Sending...');
      $('.submit_subscribe').html('Sending...');

      const toastSuccess = new bootstrap.Toast($('.success_msg')[0]);
      const toastError = new bootstrap.Toast($('.error_msg')[0]);
      const toastSubscribe = new bootstrap.Toast($('.success_msg_subscribe')[0]);

      const formData = form.serialize();

      $.ajax({
        type: "POST",
        url: "./assets/inc/form_submission.php", // make sure this path is correct
        data: formData,
        success: function (response) {
          if (response.trim() === 'success') {
            if (actionInput.length && actionInput.val() === 'subscribe') {
              $('.submit_subscribe').html('Subscribe');
              toastSubscribe.show();
            } else {
              $('.submit_form').html('Send Message');
              toastSuccess.show();
            }
          } else {
            $('.submit_form').html('Send Message');
            $('.submit_subscribe').html('Subscribe');
            toastError.show();
          }
        },
        error: function () {
          $('.submit_form').html('Send Message');
          $('.submit_subscribe').html('Subscribe');
          toastError.show();
        }
      });
    }

    form.addClass('was-validated');
  });

  /*--------------------------------------------------------------
      Number Counter
    --------------------------------------------------------------*/
  /**
     * Animates a count-up effect on the given element.
     * @param {jQuery} $el - The jQuery element to animate.
     * @param {number} [duration=2000] - Duration of the animation in milliseconds.
     */
  function countUp($el, duration = 2000) {
    const target = parseInt($el.data('count'), 10) || 0;

    $({ countNum: 0 }).animate({ countNum: target }, {
      duration: duration,
      easing: 'swing',
      step: function () {
        $el.text(Math.floor(this.countNum) + '%');
      },
      complete: function () {
        $el.text(target + '%');
      }
    });
  }
  // Setup IntersectionObserver
  function setupCountUpObserver(selector = '.count-up') {
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const $target = $(entry.target);
          if (!$target.hasClass('counted')) {
            countUp($target);
            $target.addClass('counted');
          }
          obs.unobserve(entry.target); // Stop observing after triggered
        }
      });
    }, { threshold: 0.6 });

    $(selector).each(function () {
      observer.observe(this);
    });
  }
  // Activate count-up observers
  setupCountUpObserver();

  /*--------------------------------------------------------------
      Animate On Scroll (AOS)
    --------------------------------------------------------------*/
  // Initialize AOS (Animate On Scroll)
  AOS.init({
    duration: 800,
    once: true
  });

  /*--------------------------------------------------------------
    Dynamic Nav-Link Active Class
  --------------------------------------------------------------*/

  // Get current path eg. /homepage.php, fallback to index.php if empty
  let currentPage = window.location.pathname.split('/').pop();
  if (currentPage === '') {
    currentPage = 'index.php';
  }

  // Active class function
  function markActiveLink($el) {
    const linkHref = $el.attr('href');
    if (linkHref === currentPage) {
      $el.addClass('active');
    }
  }
  // Mark main nav-link
  $('.nav-link').each(function () {
    markActiveLink($(this));
  });
  // Mark dropdown item and parent
  $('.dropdown-menu .dropdown-item').each(function () {
    const $item = $(this);
    const linkHref = $item.attr('href');
    if (linkHref === currentPage) {
      $item.addClass('active');
      $item.closest('.dropdown').find('.nav-link').addClass('active');
    }
  });
  // Mark another link at footer or other parts
  $('.links-primary').each(function () {
    markActiveLink($(this));
  });

});