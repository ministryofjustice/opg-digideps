package setup

import (
	"anonymisation/common"
	"encoding/csv"
	"fmt"
	"os"
)

func OutputToCSV(columns []common.TableColumn, filename string) error {
	common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Outputting to %s so we can see what we will be anonymising", filename))
	file, err := os.Create(filename)
	if err != nil {
		return err
	}
	defer file.Close()

	writer := csv.NewWriter(file)
	defer writer.Flush()

	// Write CSV headers
	headers := []string{"Schema", "Table", "Field", "Field_Type", "Field_Length", "Constraint", "FakerType"}
	err = writer.Write(headers)
	if err != nil {
		return err
	}

	// Write each row to the CSV file
	for _, col := range columns {
		row := []string{col.Schema, col.Table, col.Column, col.ColumnType, col.ColumnLength, col.Constraint, col.FakerType}
		err := writer.Write(row)
		if err != nil {
			return err
		}
	}

	return nil
}
