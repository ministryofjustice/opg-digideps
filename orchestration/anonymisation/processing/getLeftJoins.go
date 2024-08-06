package processing

import (
	"anonymisation/common"
	"fmt"
)

func getLeftJoinsSql(table common.Table, leftJoinsDetails []common.LeftJoinsDetails) ([]string, []common.LeftJoinsDetails) {
	var leftJoinStrings []string
	var thisTablesUpdateFields []common.LeftJoinsDetails
	for _, field := range table.FieldNames {
		for _, leftJoinsDetail := range leftJoinsDetails {
			if field.Table == leftJoinsDetail.SourceTable && field.Column == leftJoinsDetail.SourceField {

				thisTablesUpdateFields = append(thisTablesUpdateFields, leftJoinsDetail)
				var leftTableAlias string
				for i, leftJoin := range leftJoinsDetail.LeftJoins {
					if i == 0 {
						leftTableAlias = "pub2"
					} else {
						leftTableAlias = leftJoin.LeftTable
					}

					sql := fmt.Sprintf(" LEFT JOIN %s ON %s.%s = %s.%s", leftJoin.RightTable, leftTableAlias, leftJoin.LeftColumn, leftJoin.RightTable, leftJoin.RightColumn)
					leftJoinStrings = append(leftJoinStrings, sql)
				}
			}
		}
	}

	leftJoinStringsDeduped := common.RemoveDuplicateStr(leftJoinStrings)
	return leftJoinStringsDeduped, thisTablesUpdateFields
}
