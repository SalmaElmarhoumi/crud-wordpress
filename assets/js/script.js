document.addEventListener('DOMContentLoaded', function () {
    // Add confirmation dialog for delete actions
    const deleteButtons = document.querySelectorAll('.button-danger');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Display success message after form submission
    const form = document.querySelector('form');
    const submitButton = form.querySelector('input[type="submit"]');
    form.addEventListener('submit', function () {
        submitButton.value = 'Submitting...';
        submitButton.disabled = true;
        setTimeout(() => {
            submitButton.value = 'Add Item';
            submitButton.disabled = false;
        }, 2000); // Simulate a delay
    });
});
