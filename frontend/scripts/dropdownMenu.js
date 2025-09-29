
    function toggleDropdown() {
      const dropdown = document.getElementById('dropdownMenu');
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }

    window.addEventListener('click', function (e) {
      const dropdown = document.getElementById('dropdownMenu');
      if (!e.target.closest('.user-dropdown')) {
        dropdown.style.display = 'none';
      }
      });