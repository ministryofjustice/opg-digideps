import os
import re
import json


def load_json_file(file_path):
    with open(file_path, "r") as file:
        data = json.load(file)

    return data


def load_packages_from_file(file_path):
    data = load_json_file(file_path)

    requirements = data["require"]
    packages = []
    for package, requirement in requirements.items():
        if package == "php":
            continue
        packages.append(package)

    return packages


def find_files_with_extensions(directories, extensions):
    matching_files = []

    for directory in directories:
        for root, dirs, files in os.walk(directory):
            for file in files:
                if any(file.endswith(f".{ext}") for ext in extensions):
                    file_path = os.path.join(root, file)
                    matching_files.append(file_path)

    return matching_files


def remove_item_from_list(input_list, item_to_remove):
    return [item for item in input_list if item != item_to_remove]


def search_files(packages, directories, extensions, lax_search_extensions, search_dir):
    file_paths = find_files_with_extensions(directories, extensions)
    matches = []

    for file_path in file_paths:
        try:
            content = read_file(file_path)
            for line in content:
                if is_relevant_line(line, file_path, lax_search_extensions):
                    matches.extend(
                        find_package_matches(
                            line, file_path, packages, lax_search_extensions, search_dir
                        )
                    )
        except Exception as e:
            print(f"Could not read file {file_path}: {e}")

    return matches


def read_file(file_path):
    with open(file_path, "r", errors="ignore") as f:
        return f.readlines()


def is_relevant_line(line, file_path, lax_search_extensions):
    return (
        "use " in line.lower()
        or "bundles.php" in file_path.lower()
        or any(file_path.lower().endswith(f".{ext}") for ext in lax_search_extensions)
    )


def find_package_matches(line, file_path, packages, lax_search_extensions, search_dir):
    matches = []
    for package in packages:
        prefix, suffix = package.split("/")
        suffix_parts_sanitised = sanitize_suffix(suffix)
        if (
            any(file_path.lower().endswith(f".{ext}") for ext in lax_search_extensions)
            or prefix.lower() + "\\" in line.lower()
        ):
            if all(
                re.search(re.escape(word), line, re.IGNORECASE)
                for word in suffix_parts_sanitised
            ):
                matches.append((file_path.replace(search_dir, ""), package))
    return matches


def sanitize_suffix(suffix):
    suffix_parts = suffix.split("-")
    return remove_item_from_list(suffix_parts, "php")


def package_use(package_locations, unused):
    return [
        (package, len(locations))
        for package, locations in package_locations.items()
        if (len(locations) < 1 if unused else len(locations) > 0)
    ]


def format_output(packages, file_package_tuples):
    packages_usage = {}
    for package in packages:
        packages_usage[package] = []
        for file_package_tuple in file_package_tuples:
            if package == file_package_tuple[1]:
                packages_usage[package].append(file_package_tuple[0])
    deduplicated_packages_usage = {
        key: list(set(values)) for key, values in packages_usage.items()
    }

    return deduplicated_packages_usage


def filter_manual_usage(packages, manual_usages):
    manual_packages = {usage["package"] for usage in manual_usages}
    return [package[0] for package in packages if package[0] not in manual_packages]


def main():
    config = load_json_file("client.yml")
    search_dir = f"{config['repo-root']}/{config['app-dir']}"

    directories = [f"{search_dir}/{directory}" for directory in config["scan-dirs"]]
    extensions = config["extensions"]
    input_file = f"{search_dir}/{config['package-file']}"
    lax_search_extensions = config["lax-search-extensions"]
    manual_usages = config["manual-in-use-checked"]

    # The actual output
    packages = load_packages_from_file(input_file)
    file_package_tuples = search_files(
        packages, directories, extensions, lax_search_extensions, search_dir
    )
    package_locations = format_output(packages, file_package_tuples)

    used = package_use(package_locations, False)
    auto_unused = package_use(package_locations, True)

    unused = filter_manual_usage(auto_unused, manual_usages)

    print("Detailed Package Usage:")
    print(json.dumps(package_locations, indent=4))
    print("Used:")
    print(json.dumps(used, indent=4))
    print("Auto Generated Unused:")
    print(json.dumps(auto_unused, indent=4))
    print("Unused (after manual filter):")
    print(json.dumps(unused, indent=4))

    if len(unused) > 0:
        print("Warning - Unexpected unused packages in your composer.json")
    else:
        print("Everything OK - No unexpected unused packages in your composer.json")


if __name__ == "__main__":
    main()
