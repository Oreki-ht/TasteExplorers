<?php include 'config.php'; ?>
<?php include 'header.php'; ?>

<h1>North American Cuisine Recipes</h1>
<style>
        h1 {
                    text-align: center;
                    color: orange;
                    font-family: 'Arial', sans-serif;
                    font-size: 25px;
                }
            
        body {
            font-family: Arial, sans-serif;
            line-height: 2;
            margin-top: 20px;
        }

        .recipe-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .recipe-container {
            cursor: pointer;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            transition: box-shadow 0.3s ease, grid-column 0.3s ease, width 0.3s ease;
            overflow: hidden;
            word-spacing: 2px;
            letter-spacing: 0.6px;
            line-height: 1.6;
        }
        .recipe-container:hover {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .recipe-title {
            font-family: 'Georgia', serif;
            word-spacing: 4px;
            letter-spacing: 1px;
            line-height: 1.6;
            color:#fd5240;
            font-size: 16px;
        }
        .recipe-details {
            display: none;
            line-height: 1.6;
        }
        .recipe-container img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 5px;
        }
        .recipe-container.expanded {
            grid-column: 1 / -1;
            width: 100%;
        }
        .recipe-container.expanded .recipe-details {
            display: block;
        }
</style>
<div class="recipe-grid" id="recipeGrid">
<?php
$result = $conn->query("SELECT * FROM recipes_home WHERE cuisine_type='north_american'ORDER BY id DESC");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='recipe-container'>";
        echo "<h2 class='recipe-title'>" . htmlspecialchars($row["title"]) . "</h2>";
        echo "<img src='" . htmlspecialchars($row["image"]) . "' alt='" . htmlspecialchars($row["title"]) . "'>";
        echo "<div class='recipe-details'>";
        echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($row["description"])) . "</p>";
        echo "<p><strong>Ingredients:</strong> " . nl2br(htmlspecialchars($row["ingredients"])) . "</p>";
        echo "<p><strong>Directions:</strong> " . nl2br(htmlspecialchars($row["directions"])) . "</p>";
        echo "<p><strong>Cooking Time:</strong> " . htmlspecialchars($row["cooking_time"]) . " minutes</p>";
        echo "<p><strong>Cuisine:</strong> " . htmlspecialchars($row["cuisine_type"]) . "</p>";
        echo "<p><strong>Meal Type:</strong> " . htmlspecialchars($row["meal_type"]) . "</p>";
        echo "</div>"; 
        echo "</div>";
    }
} else {
    echo "No north american recipes found.";
}
?>
</div>
<script src="https:
<script>
$(document).ready(function() {
    
    $('.recipe-container').click(function() {
        var $this = $(this);
        if ($this.hasClass('expanded')) {
            $this.removeClass('expanded');
            $('.recipe-container').removeClass('hidden');
        } else {
            $('.recipe-container').removeClass('expanded').removeClass('hidden');
            $this.addClass('expanded');
        }
    });

    
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('.recipe-container').filter(function() {
            $(this).toggle($(this).find('.recipe-title').text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>

<?php include 'footer.php'; ?>
