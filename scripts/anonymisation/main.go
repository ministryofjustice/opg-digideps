package main

import (
	"database/sql"
	"encoding/csv"
	"fmt"
	"math"
	"os"
	"strconv"
	"strings"

	"github.com/go-faker/faker/v4"
	_ "github.com/lib/pq"
)

const ChunkSize = 100

type TableColumn struct {
	Schema       string
	Table        string
	Column       string
	ColumnType   string
	Constraint   string
	ColumnLength string
	FakerType    string
}

type Table struct {
	TableName  string
	PkColumn   TableColumn
	FieldNames []TableColumn
	RowCount   int
}

type FakedData struct {
	FieldName  string
	FieldValue string
	FieldType  string
}

type PostCode struct {
	FirstTwoChars  string `faker:"len=2"`
	FirstInt       int    `faker:"boundary_start=0, boundary_end=99"`
	SecondInt      int    `faker:"boundary_start=0, boundary_end=9"`
	SecondTwoChars string `faker:"len=2"`
}

func getTableColumns(db *sql.DB) ([]TableColumn, error) {
	query := `
		SELECT
			c.table_schema, c.table_name, c.column_name, c.data_type, c.character_maximum_length, links.constraint_type
		FROM information_schema.columns c
		LEFT JOIN (
		   SELECT
				tc.table_schema,
				tc.table_name,
				kcu.column_name,
				tc.constraint_type
			FROM information_schema.table_constraints AS tc
			JOIN information_schema.key_column_usage AS kcu
				ON tc.constraint_name = kcu.constraint_name
				AND tc.table_schema = kcu.table_schema
			WHERE tc.constraint_type IN ('PRIMARY KEY', 'FOREIGN KEY')
			AND tc.table_schema = 'public'
		) AS links
		ON (links.table_schema = c.table_schema AND links.table_name = c.table_name AND links.column_name = c.column_name)
		WHERE c.table_schema = 'public'
		ORDER BY c.table_name
	`

	rows, err := db.Query(query)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var columns []TableColumn
	for rows.Next() {
		var column TableColumn
		var schema, table, colName, colType, colLen, constraint sql.NullString
		err := rows.Scan(&schema, &table, &colName, &colType, &colLen, &constraint)
		if err != nil {
			return nil, err
		}
		column = TableColumn{
			Schema:       schema.String,
			Table:        table.String,
			Column:       colName.String,
			ColumnType:   colType.String,
			Constraint:   constraint.String,
			ColumnLength: colLen.String,
			FakerType:    "NA",
		}
		columns = append(columns, column)
	}

	return columns, nil
}

func updateFakerType(columns []TableColumn) ([]TableColumn, error) {
	for i, col := range columns {
		columnNameLower := strings.ToLower(col.Column)
		if (col.ColumnType == "character varying" || col.ColumnType == "text") && col.Constraint == "" {
			if strings.Contains(columnNameLower, "post") && strings.Contains(columnNameLower, "code") {
				columns[i].FakerType = "PostCode"
			} else if strings.Contains(columnNameLower, "first") && strings.Contains(columnNameLower, "name") {
				columns[i].FakerType = "FirstName"
			} else if strings.Contains(columnNameLower, "last") && strings.Contains(columnNameLower, "name") {
				columns[i].FakerType = "LastName"
			} else if strings.Contains(columnNameLower, "email") {
				columns[i].FakerType = "Email"
			} else if strings.Contains(columnNameLower, "phone") {
				columns[i].FakerType = "PhoneNumber"
			} else {
				// Default to a generic faker type
				columns[i].FakerType = "Lorem"
			}
		}

	}
	return columns, nil
}

func readConfigData(filename string) ([]TableColumn, error) {
	// Open the CSV file
	file, err := os.Open(filename)
	if err != nil {
		return nil, err
	}
	defer file.Close()

	reader := csv.NewReader(file)
	header, err := reader.Read()
	if err != nil {
		return nil, err
	}

	if len(header) != 6 {
		return nil, fmt.Errorf("unexpected number of columns in CSV file")
	}

	var columns []TableColumn
	for {
		record, err := reader.Read()
		if err != nil {
			break
		}
		column := TableColumn{
			Schema:     record[0],
			Table:      record[1],
			Column:     record[2],
			ColumnType: record[3],
			Constraint: record[4],
			FakerType:  record[5],
		}
		columns = append(columns, column)
	}

	return columns, nil
}

func applyConfigToColumns(columns, configColumns []TableColumn) ([]TableColumn, error) {
	for _, configCol := range configColumns {
		for i, col := range columns {
			if col.Schema == configCol.Schema && col.Table == configCol.Table && col.Column == configCol.Column {
				columns[i].FakerType = configCol.FakerType
				break
			}
		}
	}
	return columns, nil
}

func filterColumnsToAnonAndPkOnly(columns []TableColumn) ([]TableColumn, error) {
	var columnsFiltered []TableColumn
	for _, col := range columns {
		if col.Constraint == "PRIMARY KEY" || col.FakerType != "NA" {
			columnsFiltered = append(columnsFiltered, col)
		}
	}
	return columnsFiltered, nil
}

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
		fmt.Print(dropStatement + "\n")
		// Execute the SQL statement to drop the table
		if _, err := db.Exec(dropStatement); err != nil {
			return err
		}
	}

	// Check for any errors during row iteration
	if err := rows.Err(); err != nil {
		return err
	}

	return nil
}

func createSchemaIfNotExists(db *sql.DB, schemaName string, dropTables bool) error {
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
		fmt.Printf("Schema %s created successfully.\n", schemaName)
	} else {
		fmt.Printf("Schema %s already exists.\n", schemaName)
	}

	if dropTables {
		err := dropAllTables(db, schemaName)
		if err != nil {
			return nil
		}
	}

	return nil
}

func createTables(db *sql.DB, columns []TableColumn, schema string) ([]Table, error) {
	// Loop through each column in the columns array
	var tableDetails []Table
	for _, col := range columns {
		// Check if the column is a primary key
		if col.Constraint == "PRIMARY KEY" {
			// Create the table name by concatenating the primary key column and the table name
			tableName := col.Table
			pkColumn := col.Column
			pkType := col.ColumnType

			// Create the SQL statement to create the table
			var columnsSQL []string
			var table Table
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
			fmt.Print(sqlStatement + "\n")
			// Execute the SQL statement to create the table
			_, err := db.Exec(sqlStatement)
			if err != nil {
				return nil, err
			}
		}
	}
	return tableDetails, nil
}

func outputToCSV(columns []TableColumn, filename string) error {
	file, err := os.Create(filename)
	if err != nil {
		return err
	}
	defer file.Close()

	writer := csv.NewWriter(file)
	defer writer.Flush()

	// Write CSV headers
	headers := []string{"Schema", "Table", "Field", "Field_Type", "Field_Length", "Constraint", "FakerType"}
	err = writer.Write(headers)
	if err != nil {
		return err
	}

	// Write each row to the CSV file
	for _, col := range columns {
		row := []string{col.Schema, col.Table, col.Column, col.ColumnType, col.ColumnLength, col.Constraint, col.FakerType}
		err := writer.Write(row)
		if err != nil {
			return err
		}
	}

	return nil
}

func copySourceTablesToProcessing(db *sql.DB, tables []Table, truncate bool) ([]Table, error) {
	for tableIndex, table := range tables {
		// Truncate the destination table if truncate flag is set
		if truncate {
			_, err := db.Exec(fmt.Sprintf("TRUNCATE TABLE processing.%s", table.TableName))
			if err != nil {
				return nil, fmt.Errorf("failed to truncate table %s: %v", table.TableName, err)
			}
		}

		// Generate the INSERT INTO SELECT statement
		fieldList := ""
		for _, field := range table.FieldNames {
			fieldList += field.Column + ","
		}
		fieldList = fieldList[:len(fieldList)-1] // Remove the trailing comma

		query := fmt.Sprintf("INSERT INTO processing.%s (%s,%s,%s) SELECT %s,%s,%s FROM public.%s", table.TableName, table.PkColumn.Column, fieldList, "anonymised", table.PkColumn.Column, fieldList, "false", table.TableName)

		// Execute the INSERT INTO SELECT query
		_, err := db.Exec(query)
		if err != nil {
			return nil, fmt.Errorf("failed to copy table %s: %v", table.TableName, err)
		}

		// Get the row count of the destination table
		var rowCount int
		err = db.QueryRow(fmt.Sprintf("SELECT COUNT(*) FROM processing.%s", table.TableName)).Scan(&rowCount)
		if err != nil {
			return nil, fmt.Errorf("failed to get row count of table %s: %v", table.TableName, err)
		}

		fmt.Printf("Table %s copied successfully. Rows inserted: %d\n", table.TableName, rowCount)
		table.RowCount = rowCount
		tables[tableIndex] = table
	}
	return tables, nil
}

func insertSqlChunk(db *sql.DB, tableName string, rows [][]FakedData) error {
	if len(rows) == 0 {
		return nil // No data to insert
	}

	// Prepare column names
	columnNames := make([]string, len(rows[0]))
	for i, data := range rows[0] {
		columnNames[i] = data.FieldName
	}

	// Construct query
	query := fmt.Sprintf("INSERT INTO anon.%s (%s) VALUES",
		tableName,
		strings.Join(columnNames, ", "),
	)
	// Build value sets
	var valueSets []string
	for _, row := range rows {
		values := make([]string, len(row))
		for i, data := range row {
			if strings.EqualFold(data.FieldType, "text") || strings.EqualFold(data.FieldType, "character varying") {
				values[i] = fmt.Sprintf("'%s'", data.FieldValue)
			} else {
				values[i] = data.FieldValue
			}
		}
		valueSets = append(valueSets, fmt.Sprintf("(%s)", strings.Join(values, ", ")))
	}

	// Combine value sets
	query += strings.Join(valueSets, ",\n")
	fmt.Print(query)
	// Execute query
	_, err := db.Exec(query)
	if err != nil {
		return err
	}

	return nil
}

func generateFakeData(db *sql.DB, tableDetails []Table) error {
	for _, table := range tableDetails {
		numChunks := int(math.Ceil(float64(table.RowCount) / float64(ChunkSize)))

		for i := 0; i < numChunks; i++ {
			fmt.Printf("%s %d", table.TableName, i)
			var rows [][]FakedData
			for j := 0; j < ChunkSize; j++ {
				var fakedColumns []FakedData
				for _, col := range table.FieldNames {
					fakedValue := ""
					switch col.FakerType {
					case "FirstName":
						fakedValue = faker.FirstName()
					case "LastName":
						fakedValue = faker.LastName()
					case "PostCode":
						var pc PostCode
						faker.FakeData(&pc)
						fakedValue = fmt.Sprintf("%s%d %s%d", pc.FirstTwoChars, pc.FirstInt, pc.SecondTwoChars, pc.SecondInt)
					case "PhoneNumber":
						fakedValue = faker.Phonenumber()
					case "Email":
						fakedValue = faker.Email()
					case "Lorem":
						colLen, _ := strconv.Atoi(col.ColumnLength)
						if colLen < 20 {
							fakedValue = faker.Word()
						} else if colLen < 50 {
							fakedValue = faker.Sentence()
						} else {
							fakedValue = faker.Paragraph()
						}
						if len(fakedValue) > colLen && colLen > 0 {
							fakedValue = fakedValue[:colLen]
						} else if len(fakedValue) > 200 {
							fakedValue = fakedValue[:200]
						}
					default:
						fakedValue = ""
					}
					var fakedData FakedData
					fakedData.FieldName = col.Column
					fakedValue = strings.ReplaceAll(fakedValue, "'", "''")
					fakedData.FieldValue = fakedValue
					fakedData.FieldType = col.ColumnType
					fakedColumns = append(fakedColumns, fakedData)
				}
				rows = append(rows, fakedColumns)
			}
			err := insertSqlChunk(db, table.TableName, rows)
			checkError(err)
		}
	}
	return nil
}

func updateOriginalTables(db *sql.DB, tableDetails []Table) error {
	for _, table := range tableDetails {

		totalChunks := (table.RowCount + ChunkSize - 1) / ChunkSize

		for chunk := 0; chunk < totalChunks; chunk++ {
			offset := chunk * ChunkSize

			// Construct the update query
			sqlQuery := fmt.Sprintf("UPDATE public.%s pub SET", table.TableName)
			for _, field := range table.FieldNames {
				sqlQuery += fmt.Sprintf(" %s = CASE WHEN NULLIF(proc.%s, '') IS NULL THEN proc.%s ELSE anon.%s END,", field.Column, field.Column, field.Column, field.Column)
			}
			sqlQuery = sqlQuery[:len(sqlQuery)-1] // Remove the trailing comma
			sqlQuery += fmt.Sprintf(" FROM processing.%s AS proc, (SELECT * FROM anon.%s ORDER BY ppk_id LIMIT %d OFFSET %d) AS anon WHERE pub.%s = proc.%s AND proc.ppk_id = anon.ppk_id;",
				table.TableName, table.TableName, ChunkSize, offset, table.PkColumn.Column, table.PkColumn.Column)

			fmt.Print(sqlQuery + "\n\n")

			// Execute the update query
			_, err := db.Exec(sqlQuery)
			if err != nil {
				return err
			}
		}
	}
	return nil
}

func checkError(err error) {
	if err != nil {
		panic(err)
	}
}

func main() {
	// Replace these with your PostgreSQL connection details
	db, err := sql.Open("postgres", "user=api dbname=api password=api sslmode=disable")
	checkError(err)
	defer db.Close()

	// ===== Setup the config for anonymising =====
	columns, err := getTableColumns(db)
	checkError(err)

	columns, err = updateFakerType(columns)
	checkError(err)

	configColumns, err := readConfigData("config_data.csv")
	checkError(err)

	columns, err = applyConfigToColumns(columns, configColumns)
	checkError(err)

	columnsFiltered, err := filterColumnsToAnonAndPkOnly(columns)
	checkError(err)

	err = outputToCSV(columns, "output_all.csv")
	checkError(err)

	err = outputToCSV(columnsFiltered, "output_filtered.csv")
	checkError(err)

	// ===== Setup for the anon schema and tables =====
	err = createSchemaIfNotExists(db, "processing", true)
	checkError(err)

	err = createSchemaIfNotExists(db, "anon", true)
	checkError(err)

	tableDetails, err := createTables(db, columnsFiltered, "processing")
	checkError(err)

	_, err = createTables(db, columnsFiltered, "anon")
	checkError(err)

	tableDetails, err = copySourceTablesToProcessing(db, tableDetails, true)
	checkError(err)

	err = generateFakeData(db, tableDetails)
	checkError(err)

	err = updateOriginalTables(db, tableDetails)
	checkError(err)
}
