package initialisation

import (
	"database/sql"
	"fmt"
	"regexp"
	"testing"

	"anonymisation/common"

	"github.com/DATA-DOG/go-sqlmock"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
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

// MockDBHelper is a mock of DBHelper interface
type MockDBHelper struct {
	mock.Mock
}

func (m *MockDBHelper) DropAllTables(db *sql.DB, schemaName string) error {
	args := m.Called(db, schemaName)
	return args.Error(0)
}

func TestCreateSchemaIfNotExists(t *testing.T) {
	db, mock, err := sqlmock.New()
	assert.NoError(t, err)
	defer db.Close()

	dbHelper := &MockDBHelper{}

	t.Run("Schema does not exist, create schema", func(t *testing.T) {
		mock.ExpectQuery("SELECT EXISTS\\(SELECT schema_name FROM information_schema.schemata WHERE schema_name = \\$1\\)").
			WithArgs("test_schema").
			WillReturnRows(sqlmock.NewRows([]string{"exists"}).AddRow(false))

		mock.ExpectExec("CREATE SCHEMA test_schema").
			WillReturnResult(sqlmock.NewResult(1, 1))

		err = CreateSchemaIfNotExists(db, "test_schema", false, dbHelper)
		assert.NoError(t, err)
		assert.NoError(t, mock.ExpectationsWereMet())
	})

	t.Run("Schema exists, do not create schema", func(t *testing.T) {
		mock.ExpectQuery("SELECT EXISTS\\(SELECT schema_name FROM information_schema.schemata WHERE schema_name = \\$1\\)").
			WithArgs("test_schema").
			WillReturnRows(sqlmock.NewRows([]string{"exists"}).AddRow(true))

		err = CreateSchemaIfNotExists(db, "test_schema", false, dbHelper)
		assert.NoError(t, err)
		assert.NoError(t, mock.ExpectationsWereMet())
	})

	t.Run("Schema exists, drop tables", func(t *testing.T) {
		mock.ExpectQuery("SELECT EXISTS\\(SELECT schema_name FROM information_schema.schemata WHERE schema_name = \\$1\\)").
			WithArgs("test_schema").
			WillReturnRows(sqlmock.NewRows([]string{"exists"}).AddRow(true))

		dbHelper.On("DropAllTables", db, "test_schema").Return(nil)

		err = CreateSchemaIfNotExists(db, "test_schema", true, dbHelper)
		assert.NoError(t, err)
		assert.NoError(t, mock.ExpectationsWereMet())
	})
}

func TestCreateTables(t *testing.T) {
	db, mock, err := sqlmock.New()
	if err != nil {
		t.Fatalf("an error '%s' was not expected when opening a stub database connection", err)
	}
	defer db.Close()

	tests := []struct {
		name         string
		columns      []common.TableColumn
		schema       string
		expectedSQLs []string
		expectError  bool
	}{
		{
			name: "Create table with primary key and columns",
			columns: []common.TableColumn{
				{Table: "test_table", Column: "id", ColumnType: "INTEGER", Constraint: "PRIMARY KEY"},
				{Table: "test_table", Column: "name", ColumnType: "character varying", ColumnLength: "255"},
				{Table: "test_table", Column: "age", ColumnType: "INTEGER"},
			},
			schema:       "public",
			expectedSQLs: []string{"CREATE TABLE IF NOT EXISTS public.test_table (ppk_id SERIAL PRIMARY KEY, id INTEGER, name character varying(255), age INTEGER, anonymised bool)"},
			expectError:  false,
		},
		{
			name: "Create processing table with only primary key",
			columns: []common.TableColumn{
				{Table: "test_table", Column: "id", ColumnType: "INTEGER", Constraint: "PRIMARY KEY"},
				{Table: "test_table", Column: "name", ColumnType: "character varying", ColumnLength: "255"},
				{Table: "test_table", Column: "age", ColumnType: "INTEGER"},
			},
			schema:       "processing",
			expectedSQLs: []string{"CREATE TABLE IF NOT EXISTS processing.test_table (ppk_id SERIAL PRIMARY KEY, id INTEGER, anonymised bool)"},
			expectError:  false,
		},
	}

	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			for _, expectedSQL := range tt.expectedSQLs {
				mock.ExpectExec(regexp.QuoteMeta(expectedSQL)).WillReturnResult(sqlmock.NewResult(0, 1))
			}

			tableDetails, err := CreateTables(db, tt.columns, tt.schema)

			if tt.expectError {
				assert.Error(t, err)
			} else {
				assert.NoError(t, err)
				assert.NotEmpty(t, tableDetails)
			}

			if err := mock.ExpectationsWereMet(); err != nil {
				t.Errorf("there were unfulfilled expectations: %s", err)
			}
		})
	}
}
