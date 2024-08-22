import os
import re
import json


def load_packages_from_file(file_path):
    with open(file_path, "r") as file:
        data = json.load(file)

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
                if any(file.endswith(ext) for ext in extensions):
                    file_path = os.path.join(root, file)
                    matching_files.append(file_path)

    return matching_files


def remove_item_from_list(input_list, item_to_remove):
    return [item for item in input_list if item != item_to_remove]


def search_files(packages, directories, extensions):
    file_paths = find_files_with_extensions(directories, extensions)
    matches = []

    for file_path in file_paths:
        try:
            content = read_file(file_path)
            for line in content:
                if is_relevant_line(line, file_path):
                    matches.extend(find_package_matches(line, file_path, packages))
        except Exception as e:
            print(f"Could not read file {file_path}: {e}")

    return matches


def read_file(file_path):
    with open(file_path, "r", errors="ignore") as f:
        return f.readlines()


def is_relevant_line(line, file_path):
    return (
        "use " in line.lower()
        or "bundles.php" in file_path.lower()
        or file_path.lower().endswith(".yml")
    )


def find_package_matches(line, file_path, packages):
    matches = []
    for package in packages:
        prefix, suffix = package.split("/")
        suffix_parts_sanitised = sanitize_suffix(suffix)
        if file_path.lower().endswith(".yml") or prefix.lower() + "\\" in line.lower():
            if all(
                re.search(re.escape(word), line, re.IGNORECASE)
                for word in suffix_parts_sanitised
            ):
                matches.append((file_path, package))
    return matches


def sanitize_suffix(suffix):
    suffix_parts = suffix.split("-")
    return remove_item_from_list(suffix_parts, "php")


def package_use(package_locations, unused):
    return [
        package
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


def main():
    directories = ["./src", "./public", "./config"]
    extensions = [".php", ".yml"]
    input_file = "composer.json"

    # The actual output
    packages = load_packages_from_file(input_file)
    file_package_tuples = search_files(packages, directories, extensions)
    package_locations = format_output(packages, file_package_tuples)
    # print(json.dumps(package_locations, indent=4))

    used = package_use(package_locations, False)
    unused = package_use(package_locations, True)

    print("Used: ")
    print(json.dumps(used, indent=4))
    print("Unused: ")
    print(json.dumps(unused, indent=4))

    # if file_package_tuples:
    #     print("Matching files:")
    #     for file_package_tuple in file_package_tuples:
    #         print(file_package_tuple)
    # else:
    #     print("No matching files found.")


if __name__ == "__main__":
    main()


# symfony/expression-language used for @security component in sensio security
# twig/intl-extra used for: format_currency
# symfony/mime used by symfony/http-foundation even though it's not in it's dependencies :-(
# web-token/jwt-core also used in Jose\Component\Core\JWKSet but is possible abandonware (replace with web-token/jwt-library)
