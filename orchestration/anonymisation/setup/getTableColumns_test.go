package setup

import (
	"anonymisation/common"
	"testing"

	"github.com/DATA-DOG/go-sqlmock"
	"github.com/stretchr/testify/assert"
)

func TestGetTableColumns(t *testing.T) {
	db, mock, err := sqlmock.New()
	if err != nil {
		t.Fatalf("An error '%s' was not expected when opening a stub database connection", err)
	}
	defer db.Close()

	rows := sqlmock.NewRows([]string{"table_schema", "table_name", "column_name", "data_type", "character_maximum_length", "constraint_type"}).
		AddRow("public", "users", "id", "integer", nil, "PRIMARY KEY").
		AddRow("public", "users", "name", "character varying", "255", nil).
		AddRow("public", "users", "email", "character varying", "255", nil).
		AddRow("public", "orders", "order_id", "integer", nil, "PRIMARY KEY").
		AddRow("public", "orders", "user_id", "integer", nil, "FOREIGN KEY")

		// No need to expect the exact query, just mock the correct rows back as we're just testing they get put into TableColumn struct properly
	mock.ExpectQuery("SELECT").WillReturnRows(rows)

	columns, err := GetTableColumns(db)
	assert.NoError(t, err)
	assert.Len(t, columns, 5)

	expectedColumns := []common.TableColumn{
		{Schema: "public", Table: "users", Column: "id", ColumnType: "integer", Constraint: "PRIMARY KEY", ColumnLength: "", FakerType: "NA"},
		{Schema: "public", Table: "users", Column: "name", ColumnType: "character varying", Constraint: "", ColumnLength: "255", FakerType: "NA"},
		{Schema: "public", Table: "users", Column: "email", ColumnType: "character varying", Constraint: "", ColumnLength: "255", FakerType: "NA"},
		{Schema: "public", Table: "orders", Column: "order_id", ColumnType: "integer", Constraint: "PRIMARY KEY", ColumnLength: "", FakerType: "NA"},
		{Schema: "public", Table: "orders", Column: "user_id", ColumnType: "integer", Constraint: "FOREIGN KEY", ColumnLength: "", FakerType: "NA"},
	}

	assert.Equal(t, expectedColumns, columns)
}
