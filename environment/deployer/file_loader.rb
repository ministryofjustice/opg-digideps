# frozen_string_literal: true

def hash_from_file(file_name)
  JSON.parse(read_file(file_name))
end

def read_file(absolute_path)
  IO.read(absolute_path, encoding: Encoding::UTF_8)
rescue Errno::ENOENT
  raise ConfigNotFoundError,
        "Configuration file not found: #{absolute_path}"
end
