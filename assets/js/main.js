// Main JS functions for Community Connect
document.addEventListener("DOMContentLoaded", () => {
    // Add dynamic animation details, confirm-delete alerts, etc.
    const deleteButtons = document.querySelectorAll(".btn-confirm-delete");
    deleteButtons.forEach(button => {
        button.addEventListener("click", (e) => {
            if (!confirm("Are you sure you want to permanently delete this post/item? This action cannot be undone.")) {
                e.preventDefault();
            }
        });
    });
});
