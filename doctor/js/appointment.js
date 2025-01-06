window.onload = function() {
  const user = JSON.parse(sessionStorage.getItem('user'));

  if (!user) {
    alert('You must be logged in to set an appointment.');
    return;
  }

  const userId = user.user_id;

  // Fetch appointment data from the backend API
  fetch(`http://localhost/se/backend/api/v1/appointment?user_id=${userId}`)
      .then(response => response.json())
      .then(data => {
          const appointmentsContainer = document.getElementById('appointments-container');

          if (data.status === 'success') {
              const appointments = data.appointments;

              if (appointments.length > 0) {
                  // Create table for appointments
                  const table = document.createElement('table');
                  table.classList.add('appointments-table');
                  table.style.width = '100%';
                  table.style.borderCollapse = 'collapse';
                  table.style.marginTop = '20px';

                  // Create table header
                  const headerRow = document.createElement('tr');
                  const headers = ['Email', 'Doctor Name', 'Appointment Time', 'Contact Number'];
                  headers.forEach(header => {
                      const th = document.createElement('th');
                      th.textContent = header;
                      th.style.border = '1px solid #ddd';
                      th.style.padding = '8px';
                      th.style.textAlign = 'left';
                      th.style.backgroundColor = '#f4f4f4';
                      headerRow.appendChild(th);
                  });
                  table.appendChild(headerRow);

                  // Add each appointment as a row
                  appointments.forEach(appointment => {
                      const row = document.createElement('tr');
                      row.style.borderBottom = '1px solid #ddd';

                      // Convert appointment_time and created_at to a more readable format
                      const appointmentTime = new Date(appointment.appointment_time).toLocaleString();
                      const createdAt = new Date(appointment.created_at).toLocaleString();

                      row.innerHTML = `
                          <td style="padding: 8px;">${appointment.email}</td>
                          <td style="padding: 8px;">${appointment.name}</td>
                          <td style="padding: 8px;">${appointmentTime}</td>
                          <td style="padding: 8px;">${appointment.contact_number}</td>
                      `;
                      table.appendChild(row);
                  });

                  appointmentsContainer.appendChild(table);
              } else {
                  appointmentsContainer.innerHTML = '<p>No appointments found for this user.</p>';
              }
          } else {
              appointmentsContainer.innerHTML = `<p>${data.message}</p>`;
          }
      })
      .catch(error => {
          console.error('Error fetching appointment data:', error);
          document.getElementById('appointments-container').innerHTML = '<p>Failed to load appointment data. Please try again later.</p>';
      });
};
