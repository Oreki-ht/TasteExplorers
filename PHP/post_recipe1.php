<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taste Explorer</title>
    <link rel="stylesheet" href="stylepost.css">
</head>
<body>
<?php include 'nav.html'; ?>
    <main>
        <div class="container">
            <img src="chef5.jpg" alt="Recipe Image">
            <div class="form-container">
                <h4>Create Your Recipe</h4>
                <form action="post_recipe.php" method="POST" enctype="multipart/form-data">
                    <textarea type="text" name="title" placeholder="Recipe Title" rows="1" required></textarea>
                    <textarea name="description" placeholder="Recipe Description" rows="3" required></textarea>
                    <textarea name="ingredients" placeholder="Ingredients (comma-separated)" rows="2" required></textarea>
                    <textarea name="instructions" placeholder="Cooking Instructions" rows="5" required></textarea>
                    
                    <!-- Styled file input -->
                    <div class="file-input-container">
                        <input type="file" id="recipe_image" name="recipe_image" accept="image/*" required>
                        <label for="recipe_image">Choose Image</label>
                    </div>

                    <!-- Image preview -->
                    <div class="image-preview-container">
                        <img id="image_preview" src="" alt="Image Preview" style="display: none; max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 5px; margin-top: 20px;">
                    </div>

                    <!-- Submit button -->
                    <div class="submit-button-container">
                        <button type="submit">Post Recipe</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('recipe_image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('image_preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
