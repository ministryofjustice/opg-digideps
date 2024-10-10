package main

import (
	"database/sql"
	"fmt"
	"strconv"
	"time"

	_ "github.com/lib/pq"

	"anonymisation/common"
	"anonymisation/initialisation"
	"anonymisation/processing"
	"anonymisation/setup"
)

const DefaultChunkSize = 100

func main() {
	ChunkSize, _ := strconv.Atoi(common.GetEnvWithDefault("CHUNK_SIZE", "100"))
	TruncateInt, _ := strconv.Atoi(common.GetEnvWithDefault("TRUNCATE", "1"))
	TruncateBool := common.ConvertToBool(TruncateInt)
	EmailSuffixToIgnore := "digital.justice.gov.uk"

	// Replace these with env PostgreSQL connection details
	path := common.GetEnvWithDefault("ANON_PATH", "")
	host := common.GetEnvWithDefault("POSTGRES_HOST", "127.0.0.1")
	user := common.GetEnvWithDefault("POSTGRES_USER", "api")
	dbname := common.GetEnvWithDefault("POSTGRES_DATABASE", "api")
	password := common.GetEnvWithDefault("POSTGRES_PASSWORD", "api")
	sslmode := common.GetEnvWithDefault("POSTGRES_SSL_MODE", "disable")

	defaultUserPassword := common.GetEnvWithDefault("DEFAULT_USER_PASSWORD", "FakePassword")

	db, err := sql.Open(
		"postgres",
		fmt.Sprintf("host=%s user=%s dbname=%s password=%s sslmode=%s sslrootcert=%s", host, user, dbname, password, sslmode, "/certs/eu-west-1-bundle.pem"))
	common.CheckError(err)
	defer db.Close()

	start := time.Now()

	// ===== Setup the config for anonymising =====

	columns, err := setup.GetTableColumns(db)
	common.CheckError(err)

	_, err = setup.UpdateFakerType(columns)
	common.CheckError(err)

	configColumns, leftJoins, err := setup.ReadConfigData(fmt.Sprintf("%sconfig_data.csv", path))
	common.CheckError(err)

	columns, err = setup.ApplyConfigToColumns(columns, configColumns)
	common.CheckError(err)

	columnsFiltered, err := setup.FilterColumnsToAnonAndPkOnly(columns)
	common.CheckError(err)

	err = setup.OutputToCSV(columns, fmt.Sprintf("%soutput_all.csv", path))
	common.CheckError(err)

	err = setup.OutputToCSV(columnsFiltered, fmt.Sprintf("%soutput_filtered.csv", path))
	common.CheckError(err)

	// ===== Initialisation of schemas and tables =====
	err = initialisation.CreateSchemaIfNotExists(db, "processing", TruncateBool)
	common.CheckError(err)

	err = initialisation.CreateSchemaIfNotExists(db, "anon", TruncateBool)
	common.CheckError(err)

	tableDetails, err := initialisation.CreateTables(db, columnsFiltered, "processing")
	common.CheckError(err)

	_, err = initialisation.CreateTables(db, columnsFiltered, "anon") //Only need tableDetails once
	common.CheckError(err)

	tableDetails, err = initialisation.CopySourceTablesToProcessing(db, tableDetails, true)
	common.CheckError(err)

	// ===== Processing - Create Fake Data in Anon Schema =====
	err = processing.GenerateAsyncFakeData(db, tableDetails, ChunkSize)
	common.CheckError(err)

	// ===== Processing - Additional Complex Updates =====
	err = processing.UpdateAllToPassedInValue(db, "dd_user", "password", defaultUserPassword)
	common.CheckError(err)

	err = processing.UpdateSelectedColumnsFromPublic(db, "dd_user", "id", "email", "email", EmailSuffixToIgnore)
	common.CheckError(err)

	err = processing.UpdateSelectedColumnsFromPublic(db, "dd_user", "id", "password", "email", EmailSuffixToIgnore)
	common.CheckError(err)

	// ===== Processing - Additional Complex Updates =====
	err = processing.CustomSQLScriptUpdates(db, fmt.Sprintf("%sprocessing/sql", path))
	common.CheckError(err)

	// ===== Processing - Additional Complex Updates =====
	err = processing.UpdateAsyncOriginalTables(db, tableDetails, ChunkSize, leftJoins)
	common.CheckError(err)

	end := time.Now()
	duration := end.Sub(start)
	fmt.Printf("===== Time taken: %s ======\n", duration)
}
