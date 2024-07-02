package setup

import (
	"anonymisation/common"
	"encoding/csv"
	"os"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestParseJoinString(t *testing.T) {
	tests := []struct {
		name           string
		joinString     string
		sourceTable    string
		sourceColumn   string
		expectedResult common.LeftJoinsDetails
	}{
		{
			name:         "Single join with field",
			joinString:   "table1.column1#table2.column2~field",
			sourceTable:  "sourceTable",
			sourceColumn: "sourceColumn",
			expectedResult: common.LeftJoinsDetails{
				SourceTable: "sourceTable",
				SourceField: "sourceColumn",
				FieldName:   "field",
				TableName:   "table2",
				LeftJoins: []common.LeftJoin{
					{LeftTable: "table1", LeftColumn: "column1", RightTable: "table2", RightColumn: "column2"},
				},
			},
		},
		{
			name:         "Multiple joins with field",
			joinString:   "table1.column1#table2.column2#table3.column3#table4.column4~field",
			sourceTable:  "sourceTable",
			sourceColumn: "sourceColumn",
			expectedResult: common.LeftJoinsDetails{
				SourceTable: "sourceTable",
				SourceField: "sourceColumn",
				FieldName:   "field",
				TableName:   "table4",
				LeftJoins: []common.LeftJoin{
					{LeftTable: "table1", LeftColumn: "column1", RightTable: "table2", RightColumn: "column2"},
					{LeftTable: "table3", LeftColumn: "column3", RightTable: "table4", RightColumn: "column4"},
				},
			},
		},
		{
			name:         "Single join without field",
			joinString:   "table1.column1#table2.column2~",
			sourceTable:  "sourceTable",
			sourceColumn: "sourceColumn",
			expectedResult: common.LeftJoinsDetails{
				SourceTable: "sourceTable",
				SourceField: "sourceColumn",
				FieldName:   "",
				TableName:   "table2",
				LeftJoins: []common.LeftJoin{
					{LeftTable: "table1", LeftColumn: "column1", RightTable: "table2", RightColumn: "column2"},
				},
			},
		},
	}

	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			result := parseJoinString(tt.joinString, tt.sourceTable, tt.sourceColumn)
			assert.Equal(t, tt.expectedResult, result)
		})
	}
}

func TestReadConfigData(t *testing.T) {
	// Create a temporary CSV file
	file, err := os.CreateTemp("", "config.csv")
	assert.NoError(t, err)
	defer os.Remove(file.Name())

	// Write sample data to the CSV file
	writer := csv.NewWriter(file)
	header := []string{"schema", "table", "column", "column_type", "constraint", "faker_type", "join_string"}
	data := [][]string{
		header,
		{"anon", "users", "id", "INTEGER", "PRIMARY KEY", "NA", ""},
		{"anon", "users", "name", "character varying", "", "FakerName", ""},
		{"anon", "orders", "surname", "character varying", "", "NA", "orders.user_id#users.id~lastname"},
	}

	err = writer.WriteAll(data)
	assert.NoError(t, err)
	writer.Flush()
	file.Close()

	// Read the config data
	columns, leftJoins, err := ReadConfigData(file.Name())
	assert.NoError(t, err)

	// Validate the results
	assert.Len(t, columns, 3)
	assert.Len(t, leftJoins, 1)

	// Validate columns
	assert.Equal(t, "anon", columns[0].Schema)
	assert.Equal(t, "users", columns[0].Table)
	assert.Equal(t, "id", columns[0].Column)
	assert.Equal(t, "INTEGER", columns[0].ColumnType)
	assert.Equal(t, "PRIMARY KEY", columns[0].Constraint)
	assert.Equal(t, "NA", columns[0].FakerType)

	assert.Equal(t, "anon", columns[1].Schema)
	assert.Equal(t, "users", columns[1].Table)
	assert.Equal(t, "name", columns[1].Column)
	assert.Equal(t, "character varying", columns[1].ColumnType)
	assert.Equal(t, "", columns[1].Constraint)
	assert.Equal(t, "FakerName", columns[1].FakerType)

	assert.Equal(t, "anon", columns[2].Schema)
	assert.Equal(t, "orders", columns[2].Table)
	assert.Equal(t, "surname", columns[2].Column)
	assert.Equal(t, "character varying", columns[2].ColumnType)
	assert.Equal(t, "", columns[2].Constraint)
	assert.Equal(t, "NA", columns[2].FakerType)

	// Validate left joins Source and Target table.field combos
	assert.Equal(t, "orders", leftJoins[0].SourceTable)
	assert.Equal(t, "surname", leftJoins[0].SourceField)
	assert.Equal(t, "lastname", leftJoins[0].FieldName)
	assert.Equal(t, "users", leftJoins[0].TableName)

	// Validate the sub joins needed to get there
	assert.Len(t, leftJoins[0].LeftJoins, 1)
	assert.Equal(t, "user_id", leftJoins[0].LeftJoins[0].LeftColumn)
	assert.Equal(t, "orders", leftJoins[0].LeftJoins[0].LeftTable)
	assert.Equal(t, "id", leftJoins[0].LeftJoins[0].RightColumn)
	assert.Equal(t, "users", leftJoins[0].LeftJoins[0].RightTable)
}

func TestApplyConfigToColumns(t *testing.T) {
	tests := []struct {
		name          string
		columns       []common.TableColumn
		configColumns []common.TableColumn
		expected      []common.TableColumn
	}{
		{
			name: "All columns match",
			columns: []common.TableColumn{
				{Schema: "public", Table: "users", Column: "name", FakerType: "surname"},
				{Schema: "public", Table: "users", Column: "email", FakerType: "NA"},
			},
			configColumns: []common.TableColumn{
				{Schema: "public", Table: "users", Column: "name", FakerType: "firstname"},
				{Schema: "public", Table: "users", Column: "email", FakerType: "email"},
			},
			expected: []common.TableColumn{
				{Schema: "public", Table: "users", Column: "name", FakerType: "firstname"},
				{Schema: "public", Table: "users", Column: "email", FakerType: "email"},
			},
		},
		{
			name: "No columns match",
			columns: []common.TableColumn{
				{Schema: "public", Table: "users", Column: "name", FakerType: ""},
			},
			configColumns: []common.TableColumn{
				{Schema: "anon", Table: "orders", Column: "id", FakerType: "uuid"},
			},
			expected: []common.TableColumn{
				{Schema: "public", Table: "users", Column: "name", FakerType: ""},
			},
		},
		{
			name: "Partial columns match",
			columns: []common.TableColumn{
				{Schema: "public", Table: "users", Column: "name", FakerType: "NA"},
				{Schema: "public", Table: "users", Column: "email", FakerType: "NA"},
			},
			configColumns: []common.TableColumn{
				{Schema: "public", Table: "orders", Column: "email", FakerType: "email"},
				{Schema: "public", Table: "users", Column: "email_address", FakerType: "email"},
				{Schema: "anon", Table: "users", Column: "email", FakerType: "email"},
			},
			expected: []common.TableColumn{
				{Schema: "public", Table: "users", Column: "name", FakerType: "NA"},
				{Schema: "public", Table: "users", Column: "email", FakerType: "NA"},
			},
		},
	}

	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			got, err := ApplyConfigToColumns(tt.columns, tt.configColumns)
			if err != nil {
				t.Fatalf("ApplyConfigToColumns() error = %v", err)
			}
			assert.Equal(t, got, tt.expected)
		})
	}
}

func TestFilterColumnsToAnonAndPkOnly(t *testing.T) {
	// Define a basic test case
	columns := []common.TableColumn{
		{Schema: "public", Table: "users", Column: "id", Constraint: "PRIMARY KEY", FakerType: "NA"},
		{Schema: "public", Table: "users", Column: "name", Constraint: "", FakerType: "name"},
		{Schema: "public", Table: "users", Column: "email", Constraint: "", FakerType: "email"},
		{Schema: "public", Table: "users", Column: "age", Constraint: "", FakerType: "NA"},
	}

	expected := []common.TableColumn{
		{Schema: "public", Table: "users", Column: "id", Constraint: "PRIMARY KEY", FakerType: "NA"},
		{Schema: "public", Table: "users", Column: "name", Constraint: "", FakerType: "name"},
		{Schema: "public", Table: "users", Column: "email", Constraint: "", FakerType: "email"},
	}

	// Call the function
	got, err := FilterColumnsToAnonAndPkOnly(columns)
	if err != nil {
		t.Fatalf("FilterColumnsToAnonAndPkOnly() error = %v", err)
	}
	assert.Equal(t, got, expected)
}
