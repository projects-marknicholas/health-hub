const form = document.getElementById('reservationForm');
  
form.addEventListener('submit', async (event) => {
  event.preventDefault();

  const appointmentDate = document.getElementById('appointment_date').value;
  const appointmentTime = document.getElementById('appointment_time').value;

  const endpoint = 'http://localhost/se/backend/api/v2/set-appointment';

  const user = JSON.parse(sessionStorage.getItem('user'));

  if (!user) {
    alert('You must be logged in to set an appointment.');
    return;
  }

  const userId = user.user_id;

  const data = {
    user_id: userId,
    doctor_id: '6fe9a3d416552fd34f2c7a1c02bee22a',
    appointment_date: appointmentDate,
    appointment_time: appointmentTime,
  };

  console.log(appointmentDate);
  console.log(appointmentTime);

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    });

    // Check if the response is JSON
    const responseText = await response.text();
    console.log(responseText);
    try {
      const result = JSON.parse(responseText);

      if (result.status === 'success') {
        alert(result.message);
        form.reset();
      } else {
        alert(result.message);
      }
    } catch (error) {
      console.error('Error parsing JSON:', error);
      alert('The server returned an invalid response. Please try again later.');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Failed to set the appointment. Please try again later.');
  }
});