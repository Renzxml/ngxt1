<!-- Contact Section -->
<section id="contact" class="contact-section">
  <h2>Contact Us</h2>
  <!-- <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lacinia odio vitae vestibulum.
      Quisque vitae ex euismod, aliquam arcu a, volutpat mauris. Sed nec ullamcorper velit.</p> -->

  <div class="contact-container">
    <!-- Contact Form -->
    <form action="contact_process.php" method="POST" class="contact-form">
      <div class="form-group">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" required placeholder="Your Name">
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required placeholder="your@email.com">
      </div>

      <div class="form-group">
        <label for="message">Message</label>
        <textarea name="message" id="message" rows="5" required placeholder="Your message..."></textarea>
      </div>

      <button type="submit">Send Message</button>
    </form>

    <!-- Google Map Card -->
    <div class="map-card">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3860.1042206381767!2d120.98363257420134!3d14.650024875866514!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b52f7ff8e779%3A0x800463c32b5b71d4!2sNGXT%20Media%20Group!5e0!3m2!1sen!2sph!4v1750150103782!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
        height="100%"
        style="border:0;"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </div>
  </div>


  <script>
document.querySelector('.contact-form').addEventListener('submit', async e => {
  e.preventDefault();
  const form = e.target;
  const data = new FormData(form);

  const res = await fetch('email_process/contact_process.php', {
    method: 'POST',
    body: data
  }).then(r=>r.json());

  if (res.status === 'ok') {
    Swal.fire('Sent!', 'Thank you — we‘ll get back to you.', 'success');
    form.reset();
  } else {
    Swal.fire('Oops', res.msg || 'Something went wrong.', 'error');
  }
});
</script>



</section>