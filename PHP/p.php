<script>
    
    document.querySelectorAll('.like-button').forEach(button => {
        
        
    });

    document.querySelectorAll('.submit-comment').forEach(button => {
        
        
    });

    document.querySelectorAll('.toggle-comments').forEach(button => {
        button.addEventListener('click', function() {
            const recipeDiv = this.closest('.recipe');
            const commentsSection = recipeDiv.querySelector('.comments-section');

            commentsSection.classList.toggle('hidden');
            if (commentsSection.classList.contains('hidden')) {
                this.textContent = 'Show Comments';
            } else {
                this.textContent = 'Hide Comments';
            }
        });
    });
</script>
