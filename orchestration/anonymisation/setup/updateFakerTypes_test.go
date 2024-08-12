package setup

import (
	"anonymisation/common"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestUpdateFakerType(t *testing.T) {
	// Arrange
	columns := []common.TableColumn{
		{Schema: "public", Table: "users", Column: "id", ColumnType: "integer", Constraint: "PRIMARY KEY", FakerType: ""},
		{Schema: "public", Table: "users", Column: "first_name", ColumnType: "character varying", Constraint: "", FakerType: ""},
		{Schema: "public", Table: "users", Column: "last_name", ColumnType: "character varying", Constraint: "", FakerType: ""},
		{Schema: "public", Table: "users", Column: "email", ColumnType: "character varying", Constraint: "", FakerType: ""},
		{Schema: "public", Table: "users", Column: "phone_number", ColumnType: "character varying", Constraint: "", FakerType: ""},
		{Schema: "public", Table: "users", Column: "address", ColumnType: "character varying", Constraint: "", FakerType: ""},
		{Schema: "public", Table: "users", Column: "postal_code", ColumnType: "character varying", Constraint: "", FakerType: ""},
	}
	updatedColumns, err := UpdateFakerType(columns)
	assert.NoError(t, err)

	expectedColumns := []common.TableColumn{
		{Schema: "public", Table: "users", Column: "id", ColumnType: "integer", Constraint: "PRIMARY KEY", FakerType: ""},
		{Schema: "public", Table: "users", Column: "first_name", ColumnType: "character varying", Constraint: "", FakerType: "FirstName"},
		{Schema: "public", Table: "users", Column: "last_name", ColumnType: "character varying", Constraint: "", FakerType: "LastName"},
		{Schema: "public", Table: "users", Column: "email", ColumnType: "character varying", Constraint: "", FakerType: "Email"},
		{Schema: "public", Table: "users", Column: "phone_number", ColumnType: "character varying", Constraint: "", FakerType: "PhoneNumber"},
		{Schema: "public", Table: "users", Column: "address", ColumnType: "character varying", Constraint: "", FakerType: "Lorem"},
		{Schema: "public", Table: "users", Column: "postal_code", ColumnType: "character varying", Constraint: "", FakerType: "PostCode"},
	}

	assert.Equal(t, expectedColumns, updatedColumns)
}
