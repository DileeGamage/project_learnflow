<!DOCTYPE html>
<html>
<head>
    <title>Database Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 20px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔧 Database Schema Fix</h1>
    
    <?php
    try {
        echo "<h2>Connecting to Database...</h2>";
        $dbPath = __DIR__ . '/database/database.sqlite';
        
        if (!file_exists($dbPath)) {
            echo "<p class='error'>❌ Database file not found at: $dbPath</p>";
            exit;
        }
        
        $db = new SQLite3($dbPath);
        echo "<p class='success'>✓ Connected to SQLite database</p>";
        
        echo "<h2>Current Table Structure:</h2>";
        $result = $db->query("PRAGMA table_info(notes)");
        $existingColumns = [];
        
        echo "<pre>";
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $existingColumns[] = $row['name'];
            echo "- {$row['name']} ({$row['type']})\n";
        }
        echo "</pre>";
        
        echo "<h2>Adding Missing Columns:</h2>";
        $columns = [
            'tags' => 'TEXT',
            'is_favorite' => 'INTEGER DEFAULT 0',
            'pdf_path' => 'TEXT', 
            'extracted_text' => 'TEXT',
            'is_pdf_note' => 'INTEGER DEFAULT 0'
        ];
        
        foreach ($columns as $columnName => $columnType) {
            if (!in_array($columnName, $existingColumns)) {
                try {
                    $db->exec("ALTER TABLE notes ADD COLUMN $columnName $columnType");
                    echo "<p class='success'>✓ Added $columnName column</p>";
                } catch (Exception $e) {
                    echo "<p class='error'>❌ Failed to add $columnName: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p class='warning'>⚠ Column $columnName already exists</p>";
            }
        }
        
        echo "<h2>Final Table Structure:</h2>";
        $result = $db->query("PRAGMA table_info(notes)");
        echo "<pre>";
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo "- {$row['name']} ({$row['type']})\n";
        }
        echo "</pre>";
        
        $db->close();
        
        echo "<h2 class='success'>🎉 Database Fix Complete!</h2>";
        echo "<p>You can now go back and test your PDF upload functionality.</p>";
        echo "<p><a href='/notes/create'>← Go back to Create Note</a></p>";
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    }
    ?>
</body>
</html>
