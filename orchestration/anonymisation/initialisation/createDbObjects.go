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

	rows, err := db.Query(query)
	if err != nil {
		return err
	}
	defer rows.Close()

	for rows.Next() {
		var tableName string
		if err := rows.Scan(&tableName); err != nil {
			return err
		}
		dropStatement := fmt.Sprintf("DROP TABLE IF EXISTS %s.%s CASCADE", schema, tableName)
		if _, err := db.Exec(dropStatement); err != nil {
			return err
		}
		common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Table %s dropped successfully", tableName))
	}
	if err := rows.Err(); err != nil {
		return err
	}

	return nil
}

type DBHelper interface {
	DropAllTables(db *sql.DB, schemaName string) error
}

type RealDBHelper struct{}

func (r *RealDBHelper) DropAllTables(db *sql.DB, schemaName string) error {
	return dropAllTables(db, schemaName)
}

func CreateSchemaIfNotExists(db *sql.DB, schemaName string, dropTables bool, dbHelper ...DBHelper) error {
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

	if dropTables { // this is a variadic var to be overwritten for tests
		var helper DBHelper
		if len(dbHelper) > 0 {
			helper = dbHelper[0]
		} else {
			helper = &RealDBHelper{}
		}
		err := helper.DropAllTables(db, schemaName)
		if err != nil {
			return nil
		}
	}

	return nil
}

/**
* Create the tables in the DB
* Append details to a table object and return a list of table objects for later use
 */
func CreateTables(db *sql.DB, columns []common.TableColumn, schema string) ([]common.Table, error) {
	var tableDetails []common.Table
	for _, col := range columns {
		if col.Constraint == "PRIMARY KEY" {
			tableName := col.Table
			pkColumn := col.Column
			pkType := col.ColumnType

			// Create column strings for our SQL statements and add fieldname values to the table struct
			var columnsSQL []string
			var table common.Table
			columnsSQL = append(columnsSQL, fmt.Sprintf("%s %s", pkColumn, pkType))
			table.TableName = tableName
			table.PkColumn = col
			for _, column := range columns {
				if column.Constraint == "PRIMARY KEY" {
					continue
				}
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

			// Create the SQL statement to create the table (processing schema tables only have pk fields)
			processingPrimaryKey := "ppk_id SERIAL PRIMARY KEY"
			var sqlStatement string
			if schema == "processing" {
				sqlStatement = fmt.Sprintf("CREATE TABLE IF NOT EXISTS %s.%s (%s, %s, %s)", schema, tableName, processingPrimaryKey, columnsSQL[0], "anonymised bool")
			} else {
				sqlStatement = fmt.Sprintf("CREATE TABLE IF NOT EXISTS %s.%s (%s, %s, %s)", schema, tableName, processingPrimaryKey, strings.Join(columnsSQL, ", "), "anonymised bool")
			}
			// fmt.Print(sqlStatement + "\n")
			_, err := db.Exec(sqlStatement)
			if err != nil {
				return nil, err
			}
			common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Table %s.%s created", schema, tableName))
		}
	}
	return tableDetails, nil
}
