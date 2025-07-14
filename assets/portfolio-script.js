  document.addEventListener('DOMContentLoaded', () => {
    // Step 1: Duplicate each row's content for continuous animation
    document.querySelectorAll('.row').forEach(row => {
      const clone = row.innerHTML;
      row.innerHTML += clone;
    });

    // Step 2: Select all gallery images (after duplication)
    const images = document.querySelectorAll('.gallery-img');
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const closeBtn = document.getElementById('closeBtn');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');

    let currentIndex = 0;
    const imgArray = Array.from(images).map(img => img.src);

     // Show clicked image - updated function
  images.forEach((img, index) => {
    img.addEventListener('click', () => {
      currentIndex = index;
      lightboxImg.src = imgArray[currentIndex];
      lightbox.classList.remove('hidden');
      
      // Reset image style
      lightboxImg.style.maxWidth = '90%';
      lightboxImg.style.maxHeight = '90vh';
      lightboxImg.style.width = 'auto';
      lightboxImg.style.height = 'auto';
    });
  });

    // Close lightbox
    closeBtn.addEventListener('click', () => {
      lightbox.classList.add('hidden');
    });

    // Show next image
    nextBtn.addEventListener('click', () => {
      currentIndex = (currentIndex + 1) % imgArray.length;
      lightboxImg.src = imgArray[currentIndex];
    });

    // Show previous image
    prevBtn.addEventListener('click', () => {
      currentIndex = (currentIndex - 1 + imgArray.length) % imgArray.length;
      lightboxImg.src = imgArray[currentIndex];
    });

    // Close lightbox with Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        lightbox.classList.add('hidden');
      }
    });
  });