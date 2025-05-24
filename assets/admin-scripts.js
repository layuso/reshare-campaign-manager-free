document.addEventListener('DOMContentLoaded', function () {
    const postFieldset = document.querySelector('fieldset');

    if (postFieldset) {
        // Add Search Box
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search posts...';
        searchInput.classList.add('rcm-search-posts');
        postFieldset.insertBefore(searchInput, postFieldset.firstChild);

        // Add Select All / Deselect All Buttons
        const buttonContainer = document.createElement('div');
        buttonContainer.classList.add('rcm-bulk-buttons');
        const selectAllBtn = document.createElement('button');
        selectAllBtn.type = 'button';
        selectAllBtn.textContent = 'Select All';
        const deselectAllBtn = document.createElement('button');
        deselectAllBtn.type = 'button';
        deselectAllBtn.textContent = 'Deselect All';
        buttonContainer.appendChild(selectAllBtn);
        buttonContainer.appendChild(deselectAllBtn);
        postFieldset.insertBefore(buttonContainer, searchInput.nextSibling);

        // Search Filter Logic
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const postItems = postFieldset.querySelectorAll('p');
            postItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });

        // Select All / Deselect All Logic
        selectAllBtn.addEventListener('click', function () {
            const checkboxes = postFieldset.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = true);
        });

        deselectAllBtn.addEventListener('click', function () {
            const checkboxes = postFieldset.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = false);
        });
    }
});

