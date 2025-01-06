document.getElementById("reservationForm").addEventListener("submit", function(e) {
  e.preventDefault();

  // Combine start date and time
  let startDate = document.getElementById("start_date").value;
  let startTime = document.getElementById("start_time").value;
  let startDateTime = startDate + ' ' + startTime + ':00';

  // Combine end date and time
  let endDate = document.getElementById("end_date").value;
  let endTime = document.getElementById("end_time").value;
  let endDateTime = endDate + ' ' + endTime + ':00';

  const user = JSON.parse(sessionStorage.getItem('user'));

  if (!user) {
    alert('You must be logged in to set an appointment.');
    return;
  }

  const userId = user.user_id;
  let status = 'available';  

  // Make the API request to set availability
  fetch('http://localhost/se/backend/api/v1/set-availability', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      user_id: userId,
      start_time: startDateTime,
      end_time: endDateTime,
      status: status,
    }),
  })
    .then((response) => response.text()) // Parse as text instead of JSON
    .then((data) => {
      console.log('Raw Response:', data);
      try {
        const jsonData = JSON.parse(data);
        if (jsonData.status === 'success') {
          alert(jsonData.message);
          document.getElementById("reservationForm").reset();
        } else {
          alert(jsonData.message);
        }
      } catch (error) {
        console.error('JSON Parsing Error:', error);
      }
    })
    .catch((error) => {
      console.error('Error:', error);
    });  
});


document.addEventListener("DOMContentLoaded", function () {
  const user = JSON.parse(sessionStorage.getItem("user"));
  if (!user) {
    alert("You must be logged in to view your availability.");
    return;
  }

  const userId = user.user_id;

  // Fetch availability data
  fetch(`http://localhost/se/backend/api/v1/availability?user_id=${userId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success" && data.data.length > 0) {
        const tableBody = document.getElementById("availabilityTable").querySelector("tbody");
        tableBody.innerHTML = ""; // Clear existing rows
        data.data.forEach((item) => {
          const row = document.createElement("tr");
          row.innerHTML = `
            <td>${item.start_time}</td>
            <td>${item.end_time}</td>
            <td>${item.status}</td>
          `;
          tableBody.appendChild(row);
        });
      } else {
        const tableBody = document.getElementById("availabilityTable").querySelector("tbody");
        tableBody.innerHTML = "<tr><td colspan='3'>No availability records found.</td></tr>";
      }
    })
    .catch((error) => console.error("Error fetching availability data:", error));
});