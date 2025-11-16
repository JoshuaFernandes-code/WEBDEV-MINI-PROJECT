document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('bookingForm');
  const status = document.getElementById('formStatus');

  function showStatus(message, type) {
    status.innerHTML = message;
    status.className = 'form-status ' + (type === "success" ? "success" : "error");
    status.style.opacity = 1;
    if(type === "success") {
      setTimeout(() => { status.style.opacity = 0; }, 9000);
    }
  }

  function validateEmail(email) {
    // Simple robust regex for email
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }
  function validatePhone(phone) {
    // Accept only 10 digit Indian numbers
    return /^[0-9]{10}$/.test(phone);
  }

  function validateForm(form) {
    const name = form.guestName.value.trim();
    const phone = form.phoneNumber.value.trim();
    const email = form.email.value.trim();
    const checkin = form.checkinDate.value;
    const checkout = form.checkoutDate.value;
    const guests = form.numGuests.value;
    const roomType = form.roomType.value;

    let error = [];
    if (name.length < 2) error.push("Please enter your full name.");
    if (!validatePhone(phone)) error.push("Enter a valid 10-digit phone number.");
    if (!validateEmail(email)) error.push("Enter a valid email address.");
    if (!checkin) error.push("Select your check-in date.");
    if (!checkout) error.push("Select your check-out date.");
    if (checkin && checkout && new Date(checkout) <= new Date(checkin)) error.push("Check-out date must be after check-in date.");
    if (!guests) error.push("Select the number of guests.");
    if (!roomType) error.push("Select your room type.");

    return error;
  }

  if(form) {
    // Add placeholder pattern for dates (UX polish)
    ["checkinDate", "checkoutDate"].forEach(id=>{
      const el = document.getElementById(id);
      if(el) el.placeholder = "dd-mm-yyyy";
    });

    form.addEventListener('submit', async function(ev) {
      ev.preventDefault();
      status.innerHTML = '';
      status.className = 'form-status';

      // Validate
      const errors = validateForm(form);
      if (errors.length) {
        showStatus(errors.map(e=>`<li>${e}</li>`).join(''), 'error');
        status.scrollIntoView({behavior:"smooth", block:"center"});
        return;
      }

      // Prep submit
      const submitBtn = form.querySelector("button[type='submit']");
      submitBtn.disabled = true;
      submitBtn.innerText = "Booking...";

      // Gather data
      const fd = new FormData(form);

      try {
        const response = await fetch('booking.php', {
          method: 'POST',
          headers: { 'X-Requested-With': 'fetch' },
          body: fd
        });
        const result = await response.json();
        if(result.success) {
          showStatus("✅ " + (result.msg || "Booking submitted!"), "success");
          form.reset();
        } else {
          showStatus("❌ " + (result.msg || "Failed! Try again."), "error");
        }
      } catch (e) {
        showStatus("❌ Unable to submit booking. Please try later.", "error");
      }
      submitBtn.disabled = false;
      submitBtn.innerText = "Book Now";
    });

    // Remove error on input
    form.querySelectorAll("input,select,textarea").forEach(el=>{
      el.addEventListener('input', () => {
        status.innerHTML = ''; status.className = 'form-status';
      });
    });
  }
});
