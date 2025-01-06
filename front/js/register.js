document.getElementById('registration-form').addEventListener('submit', async function (e) {
  e.preventDefault(); // Prevent the form from reloading the page

  // Capture form data
  const name = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const contactNumber = document.getElementById('number').value.trim();
  const password = document.getElementById('password').value.trim();
  const confirmPassword = document.getElementById('confirm-password').value.trim();

  // Perform basic validation
  if (!name || !email || !contactNumber || !password || !confirmPassword) {
    alert('Please fill in all fields.');
    return;
  }

  if (password !== confirmPassword) {
    alert('Passwords do not match.');
    return;
  }

  try {
    // Send data to the backend
    const response = await fetch('http://localhost/se/backend/api/auth/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ 
        name, 
        email, 
        contact_number: contactNumber, 
        password, 
        confirm_password: confirmPassword 
      })
    });

    const result = await response.json();

    if (result.status === 'success') {
      alert(result.message);
      window.location.href = 'login.html'; 
    } else {
      alert(result.message);
    }
  } catch (error) {
    console.error('Error:', error);
    alert('An error occurred. Please try again later.');
  }
});
