import logging
from .shared import parse_human_time_span, json_load_dicts, run_log_insights_query

logger = logging.getLogger(__name__)
logger.setLevel("INFO")


def cloudwatch_business_message(event):
    job_name = event["job-name"]
    logger.info(f"Attempting to process scheduled event for job {job_name}")

    log_group = event["log-group"]
    assertion_dicts_raw = event["log-entries"]
    search_timespan = event["search-timespan"]
    channel_identifier_failure = event["channel-identifier-failure"]

    timespan = parse_human_time_span(search_timespan)
    assertion_dicts = json_load_dicts(assertion_dicts_raw)
    search_terms_and_methods = get_terms_and_methods(assertion_dicts)
    query = create_query_string(search_terms_and_methods)
    logger.info(query)
    results = run_log_insights_query(query, log_group, timespan)
    uri_list = get_uri_list_from_results(results)
    uri_counts = get_uri_counts(uri_list, search_terms_and_methods)
    assertions = create_assertions(assertion_dicts, uri_counts)
    payload = create_payload(assertions, channel_identifier_failure)

    return payload


def get_terms_and_methods(log_entries):
    term_and_method_pairs = []
    for log_entry in log_entries:
        term_and_method_pairs.append(
            {
                "search_term": log_entry["search1"],
                "method": log_entry["method1"],
                "name": log_entry["name"],
                "search_elem": 1,
            }
        )
        term_and_method_pairs.append(
            {
                "search_term": log_entry["search2"],
                "method": log_entry["method2"],
                "name": log_entry["name"],
                "search_elem": 2,
            }
        )
    return term_and_method_pairs


def create_query_string(search_terms_and_methods):
    query = """
      fields request_uri
      | filter request_uri like 'dummy-non-existent-value'"""

    for search_terms_and_method in search_terms_and_methods:
        search_term_string = search_terms_and_method["search_term"]
        method = search_terms_and_method["method"]
        search_terms = search_term_string.split("|")
        for search_term in search_terms:
            query += f"\nor {build_search_condition(search_term, method)}"

    return query


def build_search_condition(search_term, method):
    if "*" in search_term:
        search_term_parts = search_term.split("/")
        final_search_term = next(
            part for part in search_term_parts if part != "*" and len(part) > 1
        )
        return (
            f"(request_uri like '/{final_search_term}' and request_method = '{method}')"
        )
    else:
        return f"(request_uri = '{search_term}' and request_method = '{method}')"


def get_uri_list_from_results(results):
    uris = []
    for result in results:
        for result_part in result:
            if result_part["field"] == "request_uri":
                uris.append(result_part["value"])
    return uris


def get_uri_counts(uri_list, search_terms_and_methods):
    uri_counts = {}
    for uri in uri_list:
        for search_term_and_method in search_terms_and_methods:
            search_term = search_term_and_method["search_term"]
            terms = search_term.split("|")
            for term in terms:
                base_term = term.replace("/*", "")
                if base_term in uri:
                    uri_counts[search_term] = uri_counts.get(search_term, 0) + 1

    return uri_counts


def create_assertions(assertion_dicts, uri_counts):
    assertions = []
    for assertion_dict in assertion_dicts:
        assertion = {
            "name": assertion_dict["name"],
            "search1_count": int(uri_counts.get(assertion_dict["search1"], 0)),
            "search2_count": int(uri_counts.get(assertion_dict["search2"], 0)),
            "total_count": 0,
            "threshold_pct": int(assertion_dict["percentage_threshold"]),
            "threshold_count": int(assertion_dict["count_threshold"]),
            "passed": True,
        }
        assertion["total_count"] = (
            assertion["search1_count"] + assertion["search2_count"]
        )
        threshold_pct = assertion["threshold_pct"]

        if threshold_pct != 0:
            threshold_pct_result = assertion["search1_count"] * (threshold_pct / 100)
        else:
            threshold_pct_result = 0

        percent_threshold_met = (
            True if assertion["search2_count"] > threshold_pct_result else False
        )
        count_threshold_met = (
            True if assertion["total_count"] >= assertion["threshold_count"] else False
        )

        if not percent_threshold_met and count_threshold_met:
            assertion["passed"] = False
        else:
            assertion["passed"] = True

        assertions.append(assertion)
        logger.info(assertion)

    return assertions


def create_payload(assertions, channel):
    failed_assertions = []
    main_body = ""
    for assertion in assertions:
        if not assertion["passed"]:
            failed_assertions.append(assertion)
            main_body = f"{main_body}\n\n{assertion}"

    with open("templates/cloudwatch_business_failure.txt", "r") as file:
        template_text = file.read()

    formatted_text = template_text.format(
        main_body=main_body,
    )

    if len(failed_assertions) > 0:
        payload = {"text": formatted_text, "channel": channel}
    else:
        print("Business issues check complete. No issues found. Exiting ...")
        exit(0)

    return payload
