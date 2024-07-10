package common

import "database/sql"

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
	TableName        string
	PkColumn         TableColumn
	FieldNames       []TableColumn
	RowCount         int
	ExistingRowCount int
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

// Struct to hold the left join details
type LeftJoin struct {
	LeftColumn  string
	LeftTable   string
	RightColumn string
	RightTable  string
}

// Struct to hold the main table details and the array of left joins
type LeftJoinsDetails struct {
	SourceField string
	SourceTable string
	FieldName   string
	TableName   string
	LeftJoins   []LeftJoin
}

type DBHelper interface {
	DropAllTables(db *sql.DB, schemaName string) error
}

type RealDBHelper struct{}
