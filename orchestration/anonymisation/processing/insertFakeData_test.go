package processing

import (
	"anonymisation/common"

	"regexp"
	"testing"

	"github.com/DATA-DOG/go-sqlmock"
	"github.com/stretchr/testify/assert"
)

func TestInsertSqlChunk(t *testing.T) {
	// Arrange
	db, mock, err := sqlmock.New()
	assert.NoError(t, err)
	defer db.Close()

	tableName := "test_table"
	rows := [][]common.FakedData{
		{
			{FieldName: "id", FieldValue: "1", FieldType: "integer"},
			{FieldName: "name", FieldValue: "John", FieldType: "text"},
		},
		{
			{FieldName: "id", FieldValue: "2", FieldType: "integer"},
			{FieldName: "name", FieldValue: "Jane", FieldType: "text"},
		},
	}

	expectedQuery := "INSERT INTO anon.test_table (id, name) VALUES(1, 'John'), (2, 'Jane')"

	mock.ExpectExec(regexp.QuoteMeta(expectedQuery)).WillReturnResult(sqlmock.NewResult(1, 2))

	err = insertSqlChunk(db, tableName, rows)
	assert.NoError(t, err)
	assert.NoError(t, mock.ExpectationsWereMet())
}

func TestGenerateFakeDataForTable(t *testing.T) {
	// Arrange
	db, mock, err := sqlmock.New()
	assert.NoError(t, err)
	defer db.Close()

	tableDetails := common.Table{
		TableName:        "test_table",
		FieldNames:       []common.TableColumn{{Column: "name", FakerType: "FirstName", ColumnType: "text"}},
		RowCount:         4,
		ExistingRowCount: 0,
	}

	chunkSize := 5

	mock.ExpectExec("INSERT INTO anon.test_table").
		WillReturnResult(sqlmock.NewResult(1, 1))

	err = GenerateFakeDataForTable(db, tableDetails, chunkSize)

	assert.NoError(t, err)
	assert.NoError(t, mock.ExpectationsWereMet())
}
