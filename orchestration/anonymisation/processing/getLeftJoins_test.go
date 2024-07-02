package processing

import (
	"anonymisation/common"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestGetLeftJoinsSql(t *testing.T) {
	// Arrange
	table := common.Table{
		TableName: "users",
		FieldNames: []common.TableColumn{
			{Table: "users", Column: "id"},
			{Table: "users", Column: "address_id"},
		},
	}

	leftJoinsDetails := []common.LeftJoinsDetails{
		{
			SourceTable: "users",
			SourceField: "address_id",
			FieldName:   "address1",
			TableName:   "addresses",
			LeftJoins: []common.LeftJoin{
				{LeftTable: "users", LeftColumn: "address_id", RightTable: "addresses", RightColumn: "id"},
			},
		},
	}

	expectedSql := []string{
		" LEFT JOIN addresses ON pub2.address_id = addresses.id",
	}

	expectedDetails := []common.LeftJoinsDetails{
		{
			SourceTable: "users",
			SourceField: "address_id",
			FieldName:   "address1",
			TableName:   "addresses",
			LeftJoins: []common.LeftJoin{
				{LeftTable: "users", LeftColumn: "address_id", RightTable: "addresses", RightColumn: "id"},
			},
		},
	}

	leftJoinStrings, thisTablesUpdateFields := getLeftJoinsSql(table, leftJoinsDetails)
	assert.Equal(t, expectedSql, leftJoinStrings)
	assert.Equal(t, expectedDetails, thisTablesUpdateFields)
}
