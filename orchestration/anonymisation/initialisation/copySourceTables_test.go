package initialisation

import (
	"anonymisation/common"
	"fmt"

	"testing"

	"github.com/DATA-DOG/go-sqlmock"
	"github.com/stretchr/testify/assert"
)

func TestCopySourceTablesToProcessing(t *testing.T) {

	// Create a mock database
	db, mock, err := sqlmock.New()
	if err != nil {
		t.Fatalf("an error '%s' was not expected when opening a stub database connection", err)
	}
	defer db.Close()

	// Sample table data
	tables := []common.Table{
		{
			TableName: "test_table",
			FieldNames: []common.TableColumn{
				{Column: "field1"},
				{Column: "field2"},
			},
			PkColumn: common.TableColumn{Column: "id"},
		},
	}

	t.Run("truncate table and copy data", func(t *testing.T) {
		// Expected queries
		mock.ExpectExec("TRUNCATE TABLE processing.test_table").
			WillReturnResult(sqlmock.NewResult(0, 0))

		mock.ExpectExec("INSERT INTO processing.test_table").
			WillReturnResult(sqlmock.NewResult(0, 1))

		mock.ExpectQuery("SELECT COUNT\\(\\*\\) FROM processing.test_table").
			WillReturnRows(sqlmock.NewRows([]string{"count"}).AddRow(1))

		mock.ExpectQuery("SELECT COUNT\\(\\*\\) FROM anon.test_table").
			WillReturnRows(sqlmock.NewRows([]string{"count"}).AddRow(1))

		// Call the function
		result, err := CopySourceTablesToProcessing(db, tables, true)
		assert.NoError(t, err)
		assert.Equal(t, 1, result[0].RowCount)

		// Ensure all expectations were met
		if err := mock.ExpectationsWereMet(); err != nil {
			t.Errorf("there were unfulfilled expectations: %s", err)
		}
	})

	t.Run("error during truncating table", func(t *testing.T) {
		mock.ExpectExec("TRUNCATE TABLE processing.test_table").
			WillReturnError(fmt.Errorf("truncate error"))

		_, err := CopySourceTablesToProcessing(db, tables, true)
		assert.Error(t, err)
		assert.Contains(t, err.Error(), "failed to truncate table test_table")

		if err := mock.ExpectationsWereMet(); err != nil {
			t.Errorf("there were unfulfilled expectations: %s", err)
		}
	})

	t.Run("error during copying data", func(t *testing.T) {
		mock.ExpectExec("TRUNCATE TABLE processing.test_table").
			WillReturnResult(sqlmock.NewResult(0, 0))

		mock.ExpectExec("INSERT INTO processing.test_table").
			WillReturnError(fmt.Errorf("insert error"))

		_, err := CopySourceTablesToProcessing(db, tables, true)
		assert.Error(t, err)
		assert.Contains(t, err.Error(), "failed to copy table test_table")

		if err := mock.ExpectationsWereMet(); err != nil {
			t.Errorf("there were unfulfilled expectations: %s", err)
		}
	})

	t.Run("error during getting row count", func(t *testing.T) {
		mock.ExpectExec("TRUNCATE TABLE processing.test_table").
			WillReturnResult(sqlmock.NewResult(0, 0))

		mock.ExpectExec("INSERT INTO processing.test_table").
			WillReturnResult(sqlmock.NewResult(0, 1))

		mock.ExpectQuery("SELECT COUNT\\(\\*\\) FROM processing.test_table").
			WillReturnError(fmt.Errorf("count error"))

		_, err := CopySourceTablesToProcessing(db, tables, true)
		assert.Error(t, err)
		assert.Contains(t, err.Error(), "failed to get row count of table test_table")

		if err := mock.ExpectationsWereMet(); err != nil {
			t.Errorf("there were unfulfilled expectations: %s", err)
		}
	})
}
