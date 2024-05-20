package initialisation

import (
	"anonymisation/common"
	"database/sql"
	"fmt"
	"strings"
)

func dropAllTables(db *sql.DB, schema string) error {
	// Query for getting all table names in the schema
	query := fmt.Sprintf("SELECT table_name FROM information_schema.tables WHERE table_schema = '%s'", schema)

	// Execute the query to fetch table names
	rows, err := db.Query(query)
	if err != nil {
		return err
	}
	defer rows.Close()

	// Iterate over the rows to drop each table
	for rows.Next() {
		var tableName string
		if err := rows.Scan(&tableName); err != nil {
			return err
		}

		// Construct the SQL statement to drop the table
		dropStatement := fmt.Sprintf("DROP TABLE IF EXISTS %s.%s CASCADE", schema, tableName)
		// fmt.Print(dropStatement + "\n")
		// Execute the SQL statement to drop the table
		if _, err := db.Exec(dropStatement); err != nil {
			return err
		}
		common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Table %s dropped successfully", tableName))
	}

	// Check for any errors during row iteration
	if err := rows.Err(); err != nil {
		return err
	}

	return nil
}

func CreateSchemaIfNotExists(db *sql.DB, schemaName string, dropTables bool) error {
	var exists bool
	err := db.QueryRow("SELECT EXISTS(SELECT schema_name FROM information_schema.schemata WHERE schema_name = $1)", schemaName).Scan(&exists)
	if err != nil {
		return err
	}
	if !exists {
		_, err := db.Exec(fmt.Sprintf("CREATE SCHEMA %s", schemaName))
		if err != nil {
			return err
		}
		common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Schema %s created successfully", schemaName))
	} else {
		common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Schema %s already exists", schemaName))
	}

	if dropTables {
		err := dropAllTables(db, schemaName)
		if err != nil {
			return nil
		}
	}

	return nil
}

func CreateTables(db *sql.DB, columns []common.TableColumn, schema string) ([]common.Table, error) {
	// Loop through each column in the columns array
	var tableDetails []common.Table
	for _, col := range columns {
		// Check if the column is a primary key
		if col.Constraint == "PRIMARY KEY" {
			// Create the table name by concatenating the primary key column and the table name
			tableName := col.Table
			pkColumn := col.Column
			pkType := col.ColumnType

			// Create the SQL statement to create the table
			var columnsSQL []string
			var table common.Table
			columnsSQL = append(columnsSQL, fmt.Sprintf("%s %s", pkColumn, pkType))
			table.TableName = tableName
			table.PkColumn = col
			for _, column := range columns {
				// Skip if the column is a primary key
				if column.Constraint == "PRIMARY KEY" {
					continue
				}
				// Skip if the FakerType is "NA"
				if column.FakerType == "NA" {
					continue
				}
				if column.Table == tableName {
					colType := column.ColumnType
					if column.ColumnType == "character varying" {
						colType = fmt.Sprintf("character varying(%s)", column.ColumnLength)
					}

					columnsSQL = append(columnsSQL, fmt.Sprintf("%s %s", column.Column, colType))
					table.FieldNames = append(table.FieldNames, column)
				}
			}

			if len(table.FieldNames) > 0 {
				tableDetails = append(tableDetails, table)
			}

			// Create the SQL statement to create the table
			processingPrimaryKey := "ppk_id SERIAL PRIMARY KEY"
			sqlStatement := fmt.Sprintf("CREATE TABLE IF NOT EXISTS %s.%s (%s, %s, %s)", schema, tableName, processingPrimaryKey, strings.Join(columnsSQL, ", "), "anonymised bool")
			// fmt.Print(sqlStatement + "\n")
			// Execute the SQL statement to create the table
			_, err := db.Exec(sqlStatement)
			if err != nil {
				return nil, err
			}
			common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Table %s.%s created", schema, tableName))
		}
	}
	return tableDetails, nil
}
