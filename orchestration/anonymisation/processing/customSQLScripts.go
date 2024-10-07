package processing

import (
	"database/sql"
	"fmt"
	"os"
	"path/filepath"
	"strings"
)

// CustomSQLScriptUpdates reads SQL files from the 'sql' directory and executes them in order.
func CustomSQLScriptUpdates(db *sql.DB) error {
	// Directory containing the SQL files
	sqlDir := "processing/sql"

	files, err := os.ReadDir(sqlDir)
	if err != nil {
		return fmt.Errorf("failed to read directory: %v", err)
	}

	for _, file := range files {
		if !file.IsDir() && strings.HasSuffix(file.Name(), ".sql") {
			filePath := filepath.Join(sqlDir, file.Name())

			sqlBytes, err := os.ReadFile(filePath)
			if err != nil {
				return fmt.Errorf("failed to read file %s: %v", file.Name(), err)
			}

			sqlStatement := string(sqlBytes)

			// Execute the SQL statement
			_, err = db.Exec(sqlStatement)
			if err != nil {
				return fmt.Errorf("failed to execute SQL in file %s: %v", file.Name(), err)
			}

			fmt.Printf("\nExecuted SQL from file: %s\n\n", file.Name())
		}
	}

	return nil
}

// If you want to to simply blanket every row for a column in a table to a particular value
func UpdateAllToPassedInValue(
	db *sql.DB,
	tableName string,
	updateColumn string,
	updateString string) error {
	query := fmt.Sprintf(`UPDATE anon.%s SET %s = '%s';`, tableName, updateColumn, updateString)

	fmt.Println(query)
	// Execute the update query
	_, err := db.Exec(query)
	if err != nil {
		return err
	}

	return nil
}
