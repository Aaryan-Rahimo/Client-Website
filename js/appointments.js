/**
 * Author: Aaryan, Kissan, Inderbir, Angad
 * Date Created: 2026-04-05
 * Description: Frontend interactions for appointment management actions in admin appointments page, including dynamic time slot loading and declination note handling.
 */
document.addEventListener("DOMContentLoaded", function() {
  const dateInput = document.getElementById("appt_date");
  const timeSelect = document.getElementById("time_start");
  const bookingForm = document.getElementById("bookingForm");
  
  if (!dateInput || !timeSelect) return;

  const today = new Date();
  const dd = String(today.getDate()).padStart(2, '0');
  const mm = String(today.getMonth() + 1).padStart(2, '0');
  const yyyy = today.getFullYear();
  const todayStr = yyyy + '-' + mm + '-' + dd;
  dateInput.setAttribute("min", todayStr);

  async function loadSlotsForDate(selectedDate, selectedTime) {
    if (!selectedDate) {
      timeSelect.setAttribute("disabled", "true");
      timeSelect.innerHTML = '<option value="" disabled selected>Select a date first</option>';
      return;
    }

    timeSelect.setAttribute("disabled", "disabled");
    timeSelect.innerHTML = '<option value="" disabled selected>Loading available times...</option>';

    try {
      const response = await fetch(`actions/get_booked_slots.php?date=${selectedDate}`);
      const bookedSlots = await response.json();
      const allOptions = window.clinicTimeSlots || {};
      
      let optionsHtml = '<option value="" disabled selected>Select a time</option>';
      const now = new Date();
      const isToday = selectedDate === todayStr;
      let canKeepSelected = false;

      for (const [timeValue, timeLabel] of Object.entries(allOptions)) {
        let disabled = "";
        let textSuffix = "";

        if (bookedSlots.includes(timeValue)) {
          disabled = "disabled";
          textSuffix = " (Booked)";
        }

        if (isToday) {
          const [hours, minutes] = timeValue.split(':');
          const slotTime = new Date();
          slotTime.setHours(parseInt(hours), parseInt(minutes), 0, 0);

          if (slotTime < now) {
            disabled = "disabled";
            textSuffix = " (Passed)";
          }
        }

        if (selectedTime && selectedTime === timeValue && disabled === "") {
          canKeepSelected = true;
        }

        optionsHtml += `<option value="${timeValue}" ${disabled}>${timeLabel}${textSuffix}</option>`;
      }
      
      timeSelect.innerHTML = optionsHtml;
      timeSelect.removeAttribute("disabled");

      if (selectedTime && canKeepSelected) {
        timeSelect.value = selectedTime;
      }
      
    } catch (e) {
      console.error("Error fetching available times", e);
      timeSelect.innerHTML = '<option value="" disabled selected>Error loading times</option>';
    }
  }

  dateInput.addEventListener("change", function() {
    loadSlotsForDate(this.value, "");
  });

  if (dateInput.value) {
    loadSlotsForDate(dateInput.value, timeSelect.dataset.selectedTime || "");
  }

  if (bookingForm) {
    bookingForm.addEventListener("submit", function(e) {
      const selectedOption = timeSelect.options[timeSelect.selectedIndex];
      if (!selectedOption || selectedOption.disabled) {
        e.preventDefault();
        alert("That time slot is already taken. Please choose an available time.");
      }
    });
  }
});
