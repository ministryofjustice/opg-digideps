package processing

import (
	"anonymisation/common"
	"regexp"
	"strings"
	"testing"

	"github.com/DATA-DOG/go-sqlmock"
	"github.com/stretchr/testify/assert"
)

func TestGetSqlUpdateStatement(t *testing.T) {
	table := common.Table{
		TableName: "user",
		FieldNames: []common.TableColumn{
			{Table: "user", Column: "first_name", FakerType: "FirstName"},
			{Table: "user", Column: "last_name", FakerType: "LastName"},
		},
		PkColumn: common.TableColumn{Table: "user", Column: "id", FakerType: "NA"},
	}

	leftJoinsDetails := []common.LeftJoinsDetails{
		{
			SourceTable: "user",
			SourceField: "first_name",
			FieldName:   "fname",
			TableName:   "orders",
			LeftJoins: []common.LeftJoin{
				{LeftColumn: "id", LeftTable: "user", RightColumn: "user_id", RightTable: "orders"},
			},
		},
	}

	chunkSize := 10
	offset := 0
	leftJoinSqlLinesField := []string{" LEFT JOIN orders ON pub2.id = orders.user_id"}

	strs := []string{
		"UPDATE public.user pub1 ",
		"SET ",
		"first_name = CASE WHEN NULLIF(pub2.first_name, '') IS NULL THEN pub2.first_name ELSE COALESCE(orders.fname, anon.first_name) END, ",
		"last_name = CASE WHEN NULLIF(pub2.last_name, '') IS NULL THEN pub2.last_name ELSE anon.last_name END ",
		"FROM public.user as pub2 ",
		"INNER JOIN processing.user AS proc ON pub2.id = proc.id ",
		"INNER JOIN (SELECT * FROM anon.user ORDER BY ppk_id LIMIT 10 OFFSET 0) AS anon ON proc.ppk_id = anon.ppk_id ",
		"LEFT JOIN orders ON pub2.id = orders.user_id ",
		"WHERE pub1.id = pub2.id;",
	}
	expectedQuery := strings.Join(strs, "")

	actualQuery := getSqlUpdateStatement(table, leftJoinsDetails, chunkSize, offset, leftJoinSqlLinesField)

	if strings.TrimSpace(expectedQuery) != strings.TrimSpace(actualQuery) {
		t.Errorf("Expected query: %s\nActual query: %s", expectedQuery, actualQuery)
	}
}

// No mocks here as I want to actually check the final query looks how I expect.
// We want to see results of sql update statement and the left joins functions combined.
func TestUpdateOriginalTable(t *testing.T) {
	// Set up the mock database
	db, mock, err := sqlmock.New()
	assert.NoError(t, err)
	defer db.Close()

	table := common.Table{
		TableName: "user",
		FieldNames: []common.TableColumn{
			{Table: "user", Column: "first_name", FakerType: "FirstName"},
			{Table: "user", Column: "last_name", FakerType: "LastName"},
		},
		RowCount: 1,
		PkColumn: common.TableColumn{Table: "user", Column: "id", FakerType: "NA"},
	}

	leftJoinsDetails := []common.LeftJoinsDetails{
		{
			SourceTable: "user",
			SourceField: "first_name",
			FieldName:   "fname",
			TableName:   "orders",
			LeftJoins: []common.LeftJoin{
				{LeftColumn: "id", LeftTable: "user", RightColumn: "user_id", RightTable: "orders"},
			},
		},
	}

	chunkSize := 10

	strs := []string{
		"UPDATE public.user pub1 ",
		"SET ",
		"first_name = CASE WHEN NULLIF(pub2.first_name, '') IS NULL THEN pub2.first_name ELSE COALESCE(orders.fname, anon.first_name) END, ",
		"last_name = CASE WHEN NULLIF(pub2.last_name, '') IS NULL THEN pub2.last_name ELSE anon.last_name END ",
		"FROM public.user as pub2 ",
		"INNER JOIN processing.user AS proc ON pub2.id = proc.id ",
		"INNER JOIN (SELECT * FROM anon.user ORDER BY ppk_id LIMIT 10 OFFSET 0) AS anon ON proc.ppk_id = anon.ppk_id ",
		"LEFT JOIN orders ON pub2.id = orders.user_id ",
		"WHERE pub1.id = pub2.id;",
	}
	expectedQuery := strings.Join(strs, "")

	mock.ExpectExec(regexp.QuoteMeta(expectedQuery)).
		WillReturnResult(sqlmock.NewResult(1, 1))

	// Call the function
	err = UpdateOriginalTable(db, table, chunkSize, leftJoinsDetails)
	assert.NoError(t, err)

	err = mock.ExpectationsWereMet()
	assert.NoError(t, err)
}
