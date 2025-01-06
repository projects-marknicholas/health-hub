document.getElementById('loginForm').addEventListener('submit', async function (e) {
  e.preventDefault(); // Prevent form from refreshing the page

  // Get the email and password values
  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value.trim();

  try {
    // Send login request to the backend
    const response = await fetch('http://localhost/se/backend/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ email, password })
    });

    const result = await response.json();

    if (result.status === 'success') {
      alert(result.message);
      sessionStorage.setItem('user', JSON.stringify(result.user));

      // Redirect based on the role
      const user = result.user;
      const role = user.role;

      if (role === 'doctor') {
        window.location.href = '../doctor/dashboard.html'; 
      } else if (role === 'user') {
        window.location.href = '../front/dashboard.html'; 
      } else {
        // If no role or unknown role, do nothing or show a message
        alert('Unknown role or no specific role assigned');
      }
    } else {
      alert(result.message);
    }
  } catch (error) {
    console.error('Login Error:', error);
    alert('An error occurred. Please try again later.');
  }
});
