document.addEventListener('DOMContentLoaded', function () {
  // Check if user data exists in sessionStorage
  const user = sessionStorage.getItem('user');

  if (!user) {
    window.location.href = '../front/login.html';
  }
});