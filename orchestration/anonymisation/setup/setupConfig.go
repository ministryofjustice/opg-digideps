package setup

import (
	"anonymisation/common"
	"encoding/csv"
	"fmt"
	"os"
	"strings"
)

func parseJoinString(joinString string, sourceTable string, sourceColumn string) common.LeftJoinsDetails {
	parts := strings.Split(joinString, "~")
	fieldName := parts[1]

	if fieldName == "" {
		fmt.Printf("WARNING - Join string %s badly formatted", joinString)
	}

	joins := strings.Split(parts[0], "#")
	tableName := strings.Split(joins[len(joins)-1], ".")[0]

	var leftJoins []common.LeftJoin
	for i := 0; i+1 < len(joins); i += 2 {
		leftParts := strings.Split(joins[i], ".")
		rightParts := strings.Split(joins[i+1], ".")

		leftJoin := common.LeftJoin{
			LeftColumn:  leftParts[1],
			LeftTable:   leftParts[0],
			RightColumn: rightParts[1],
			RightTable:  rightParts[0],
		}
		leftJoins = append(leftJoins, leftJoin)
	}

	return common.LeftJoinsDetails{
		SourceTable: sourceTable,
		SourceField: sourceColumn,
		FieldName:   fieldName,
		TableName:   tableName,
		LeftJoins:   leftJoins,
	}
}

func ReadConfigData(filename string) ([]common.TableColumn, []common.LeftJoinsDetails, error) {
	common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Reading config from %s", filename))
	// Open the CSV file
	file, err := os.Open(filename)
	if err != nil {
		return nil, nil, err
	}
	defer file.Close()

	reader := csv.NewReader(file)
	header, err := reader.Read()
	if err != nil {
		return nil, nil, err
	}

	if len(header) != 7 {
		return nil, nil, fmt.Errorf("unexpected number of columns in CSV file")
	}

	var columns []common.TableColumn
	var leftJoins []common.LeftJoinsDetails
	for {
		record, err := reader.Read()
		if err != nil {
			fmt.Print(err)
			break
		}

		if len(record[6]) > 0 {
			leftJoin := parseJoinString(record[6], record[1], record[2])
			leftJoins = append(leftJoins, leftJoin)
		}

		column := common.TableColumn{
			Schema:     record[0],
			Table:      record[1],
			Column:     record[2],
			ColumnType: record[3],
			Constraint: record[4],
			FakerType:  record[5],
		}
		columns = append(columns, column)
	}
	common.LogInformation(common.GetCurrentFuncName(), "Config values appended to an array of TableColumn")
	return columns, leftJoins, nil
}

func ApplyConfigToColumns(columns, configColumns []common.TableColumn) ([]common.TableColumn, error) {
	common.LogInformation(common.GetCurrentFuncName(), "Applying our config settings to the columns setup from public schema")
	for _, configCol := range configColumns {
		for i, col := range columns {
			if col.Schema == configCol.Schema && col.Table == configCol.Table && col.Column == configCol.Column {
				columns[i].FakerType = configCol.FakerType
				break
			}
		}
	}
	return columns, nil
}

func FilterColumnsToAnonAndPkOnly(columns []common.TableColumn) ([]common.TableColumn, error) {
	common.LogInformation(common.GetCurrentFuncName(), "Filter out non PK columns that we do not need to anonymise")
	var columnsFiltered []common.TableColumn
	for _, col := range columns {
		if col.Constraint == "PRIMARY KEY" || col.FakerType != "NA" {
			columnsFiltered = append(columnsFiltered, col)
		}
	}
	return columnsFiltered, nil
}
