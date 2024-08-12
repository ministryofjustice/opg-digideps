package setup

import (
	"anonymisation/common"
	"database/sql"
)

func GetTableColumns(db *sql.DB) ([]common.TableColumn, error) {
	common.LogInformation(common.GetCurrentFuncName(), "Getting public schema and table details")
	query := `
		SELECT
			c.table_schema, c.table_name, c.column_name, c.data_type, c.character_maximum_length, links.constraint_type
		FROM information_schema.columns c
		LEFT JOIN (
		   SELECT
				tc.table_schema,
				tc.table_name,
				kcu.column_name,
				tc.constraint_type
			FROM information_schema.table_constraints AS tc
			JOIN information_schema.key_column_usage AS kcu
				ON tc.constraint_name = kcu.constraint_name
				AND tc.table_schema = kcu.table_schema
			WHERE tc.constraint_type IN ('PRIMARY KEY', 'FOREIGN KEY')
			AND tc.table_schema = 'public'
		) AS links
		ON (links.table_schema = c.table_schema AND links.table_name = c.table_name AND links.column_name = c.column_name)
		WHERE c.table_schema = 'public'
		ORDER BY c.table_name
	`

	rows, err := db.Query(query)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	common.LogInformation(common.GetCurrentFuncName(), "Creating array of TableColumn objects")
	var columns []common.TableColumn
	for rows.Next() {
		var column common.TableColumn
		var schema, table, colName, colType, colLen, constraint sql.NullString
		err := rows.Scan(&schema, &table, &colName, &colType, &colLen, &constraint)
		if err != nil {
			return nil, err
		}
		column = common.TableColumn{
			Schema:       schema.String,
			Table:        table.String,
			Column:       colName.String,
			ColumnType:   colType.String,
			Constraint:   constraint.String,
			ColumnLength: colLen.String,
			FakerType:    "NA",
		}
		columns = append(columns, column)
	}

	return columns, nil
}
