package setup

import (
	"anonymisation/common"
	"strings"
)

func UpdateFakerType(columns []common.TableColumn) ([]common.TableColumn, error) {
	common.LogInformation(common.GetCurrentFuncName(), "Setting Faker types in each TableColumn object")
	for i, col := range columns {
		columnNameLower := strings.ToLower(col.Column)
		if (col.ColumnType == "character varying" || col.ColumnType == "text") && col.Constraint == "" {
			if strings.Contains(columnNameLower, "post") && strings.Contains(columnNameLower, "code") {
				columns[i].FakerType = "PostCode"
			} else if strings.Contains(columnNameLower, "first") && strings.Contains(columnNameLower, "name") {
				columns[i].FakerType = "FirstName"
			} else if strings.Contains(columnNameLower, "last") && strings.Contains(columnNameLower, "name") {
				columns[i].FakerType = "LastName"
			} else if strings.Contains(columnNameLower, "email") {
				columns[i].FakerType = "Email"
			} else if strings.Contains(columnNameLower, "phone") {
				columns[i].FakerType = "PhoneNumber"
			} else {
				// Default to a generic faker type
				columns[i].FakerType = "Lorem"
			}
		}

	}
	return columns, nil
}
