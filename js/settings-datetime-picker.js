window.addEventListener('DOMContentLoaded', function() {
  var el = document.querySelector('.smart-cf-datetime_picker');

  if (el) {
    var data = el.getAttribute('data-js');
    data = JSON.parse(data);
    data['enableTime'] = true;
  }

  flatpickr('.smart-cf-datetime_picker', data);
});
