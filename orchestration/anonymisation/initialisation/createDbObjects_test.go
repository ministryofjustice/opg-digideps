package initialisation

import (
	"database/sql"
	"fmt"
	"testing"

	"github.com/DATA-DOG/go-sqlmock"
	"github.com/stretchr/testify/assert"
)

func TestDropAllTables(t *testing.T) {
	// Initialize the mock database
	db, mock, err := sqlmock.New()
	if err != nil {
		t.Fatalf("An error '%s' was not expected when opening a stub database connection", err)
	}
	defer db.Close()

	schema := "public"

	// Mock the query for fetching table names
	query := fmt.Sprintf("SELECT table_name FROM information_schema.tables WHERE table_schema = '%s'", schema)
	rows := sqlmock.NewRows([]string{"table_name"}).AddRow("table1").AddRow("table2")
	mock.ExpectQuery(query).WillReturnRows(rows)

	// Mock the execution of drop table statements
	mock.ExpectExec(fmt.Sprintf("DROP TABLE IF EXISTS %s.%s CASCADE", schema, "table1")).WillReturnResult(sqlmock.NewResult(1, 1))
	mock.ExpectExec(fmt.Sprintf("DROP TABLE IF EXISTS %s.%s CASCADE", schema, "table2")).WillReturnResult(sqlmock.NewResult(1, 1))

	// Call the function
	err = dropAllTables(db, schema)

	// Assert that no errors occurred
	assert.NoError(t, err)

	// Ensure all expectations were met
	if err := mock.ExpectationsWereMet(); err != nil {
		t.Errorf("There were unfulfilled expectations: %s", err)
	}
}

var dropAllTablesMock = func(db *sql.DB, schema string) error {
	return nil
}

// func TestCreateSchemaIfNotExists(t *testing.T) {
// 	// Initialize the mock database
// 	db, mock, err := sqlmock.New()
// 	if err != nil {
// 		t.Fatalf("An error '%s' was not expected when opening a stub database connection", err)
// 	}
// 	defer db.Close()

// 	schemaName := "testschema"
// 	dropTables := true

// 	// Mock the query for checking if the schema exists
// 	mock.ExpectQuery("SELECT EXISTS\\(SELECT schema_name FROM information_schema.schemata WHERE schema_name = \\$1\\)").
// 		WithArgs(schemaName).
// 		WillReturnRows(sqlmock.NewRows([]string{"exists"}).AddRow(false))

// 	// Mock the execution of the create schema statement
// 	mock.ExpectExec(fmt.Sprintf("CREATE SCHEMA %s", schemaName)).
// 		WillReturnResult(sqlmock.NewResult(1, 1))

// 	// Temporarily replace the dropAllTables function with a mock
// 	originalDropAllTables := dropAllTables
// 	dropAllTables = dropAllTablesMock
// 	defer func() { dropAllTables = originalDropAllTables }()

// 	// Call the function
// 	err = CreateSchemaIfNotExists(db, schemaName, dropTables)

// 	// Assert that no errors occurred
// 	assert.NoError(t, err)

// 	// Ensure all expectations were met
// 	if err := mock.ExpectationsWereMet(); err != nil {
// 		t.Errorf("There were unfulfilled expectations: %s", err)
// 	}
// }

func TestCreateSchemaIfExists(t *testing.T) {
	// Initialize the mock database
	db, mock, err := sqlmock.New()
	if err != nil {
		t.Fatalf("An error '%s' was not expected when opening a stub database connection", err)
	}
	defer db.Close()

	schemaName := "testschema"
	dropTables := false

	// Mock the query for checking if the schema exists
	mock.ExpectQuery("SELECT EXISTS\\(SELECT schema_name FROM information_schema.schemata WHERE schema_name = \\$1\\)").
		WithArgs(schemaName).
		WillReturnRows(sqlmock.NewRows([]string{"exists"}).AddRow(true))

	// Call the function
	err = CreateSchemaIfNotExists(db, schemaName, dropTables)

	// Assert that no errors occurred
	assert.NoError(t, err)

	// Ensure all expectations were met
	if err := mock.ExpectationsWereMet(); err != nil {
		t.Errorf("There were unfulfilled expectations: %s", err)
	}
}

func TestCreateSchemaIfNotExistsWithDropTables(t *testing.T) {
	// Initialize the mock database
	db, mock, err := sqlmock.New()
	if err != nil {
		t.Fatalf("An error '%s' was not expected when opening a stub database connection", err)
	}
	defer db.Close()

	schemaName := "testschema"
	dropTables := true

	// Mock the query for checking if the schema exists
	mock.ExpectQuery("SELECT EXISTS\\(SELECT schema_name FROM information_schema.schemata WHERE schema_name = \\$1\\)").
		WithArgs(schemaName).
		WillReturnRows(sqlmock.NewRows([]string{"exists"}).AddRow(true))

	// Temporarily replace the dropAllTables function with a mock
	originalDropAllTables := dropAllTables
	dropAllTables = dropAllTablesMock
	defer func() { dropAllTables = originalDropAllTables }()

	// Call the function
	err = CreateSchemaIfNotExists(db, schemaName, dropTables)

	// Assert that no errors occurred
	assert.NoError(t, err)

	// Ensure all expectations were met
	if err := mock.ExpectationsWereMet(); err != nil {
		t.Errorf("There were unfulfilled expectations: %s", err)
	}
}
