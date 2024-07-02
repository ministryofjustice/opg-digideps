package processing

import (
	"anonymisation/common"
	"database/sql"
	"fmt"
	"math"
	"strconv"
	"strings"
	"sync"

	"github.com/go-faker/faker/v4"
)

func insertSqlChunk(db *sql.DB, tableName string, rows [][]common.FakedData) error {
	if len(rows) == 0 {
		return nil // No data to insert
	}

	// Prepare column names
	columnNames := make([]string, len(rows[0]))
	for i, data := range rows[0] {
		columnNames[i] = data.FieldName
	}

	// Construct query
	query := fmt.Sprintf("INSERT INTO anon.%s (%s) VALUES",
		tableName,
		strings.Join(columnNames, ", "),
	)
	// Build value sets
	var valueSets []string
	for _, row := range rows {
		values := make([]string, len(row))
		for i, data := range row {
			if strings.EqualFold(data.FieldType, "text") || strings.EqualFold(data.FieldType, "character varying") {
				values[i] = fmt.Sprintf("'%s'", data.FieldValue)
			} else {
				values[i] = data.FieldValue
			}
		}
		valueSets = append(valueSets, fmt.Sprintf("(%s)", strings.Join(values, ", ")))
	}

	// Combine value sets
	query += strings.Join(valueSets, ",\n")
	// fmt.Print(query)

	_, err := db.Exec(query)
	if err != nil {
		return err
	}

	return nil
}

func GenerateFakeDataForTable(db *sql.DB, table common.Table, chunkSize int) error {
	remainingRows := int(table.RowCount) - int(table.ExistingRowCount)
	numChunks := int(math.Ceil(float64(remainingRows) / float64(chunkSize)))

	for i := 0; i < numChunks; i++ {
		var rowsThisChunk int
		if (i + 1) == numChunks {
			rowsThisChunk = remainingRows % chunkSize
			if rowsThisChunk == 0 {
				rowsThisChunk = chunkSize
			}
		} else {
			rowsThisChunk = chunkSize
		}
		common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Table %s.%s, chunk %d of %d will have fake data inserted. Rows this chunk: %d", "anon", table.TableName, i+1, numChunks, rowsThisChunk))
		var rows [][]common.FakedData
		for j := 0; j < rowsThisChunk; j++ {
			var fakedColumns []common.FakedData
			for _, col := range table.FieldNames {

				fakedValue := ""
				switch col.FakerType {
				case "FirstName":
					fakedValue = faker.FirstName()
				case "LastName":
					fakedValue = faker.LastName()
				case "PostCode":
					var pc common.PostCode
					faker.FakeData(&pc)
					fakedValue = fmt.Sprintf("%s%d %s%d", pc.FirstTwoChars, pc.FirstInt, pc.SecondTwoChars, pc.SecondInt)
				case "PhoneNumber":
					fakedValue = faker.Phonenumber()
				case "Email":
					fakedValue = faker.Email()
				case "Lorem":
					colLen, _ := strconv.Atoi(col.ColumnLength)
					if colLen < 20 {
						fakedValue = faker.Word()
					} else if colLen < 50 {
						fakedValue = faker.Sentence()
					} else {
						fakedValue = faker.Paragraph()
					}
					if len(fakedValue) > colLen && colLen > 0 {
						fakedValue = fakedValue[:colLen]
					} else if len(fakedValue) > 200 {
						fakedValue = fakedValue[:200]
					}
				default:
					fakedValue = ""
				}
				var fakedData common.FakedData
				fakedData.FieldName = col.Column
				fakedValue = strings.ReplaceAll(fakedValue, "'", "''")
				fakedData.FieldValue = fakedValue
				fakedData.FieldType = col.ColumnType
				fakedColumns = append(fakedColumns, fakedData)
			}
			rows = append(rows, fakedColumns)
		}

		err := insertSqlChunk(db, table.TableName, rows)
		common.CheckError(err)
		common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Table %s.%s - Finished with chunk  %d of %d", "anon", table.TableName, i+1, numChunks))
	}
	return nil
}

func GenerateAsyncFakeData(db *sql.DB, tableDetailsCollection []common.Table, chunkSize int) error {
	concurrency := 4
	semaphore := make(chan struct{}, concurrency) // Create a channel for concurrent goroutines
	done := make(chan struct{})                   // Create a channel to signal when all updates are done
	var wg sync.WaitGroup
	go func() { // Launch a goroutine to close the done channel when all updates are done
		defer close(done)
		wg.Add(len(tableDetailsCollection))
		for range tableDetailsCollection {
			<-done
			wg.Done()
		}
		wg.Wait() // Wait for all tables to finish processing
	}()

	for _, table := range tableDetailsCollection {
		semaphore <- struct{}{}

		// Launch a goroutine to process the table
		go func(table common.Table) {
			defer func() {
				// Release the token back to the semaphore
				<-semaphore
				done <- struct{}{} // Signal that this table's processing is done
			}()
			GenerateFakeDataForTable(db, table, chunkSize)
		}(table)
	}
	// Wait for all updates to finish
	wg.Wait()
	return nil
}
