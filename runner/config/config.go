package config

type Config struct {
	Role  struct {
		Sensitive bool
		Type      interface{}
		Value     string
	}
	Tasks struct {
		Sensitive bool
		Type      []interface{}
		Value     map[string]*ecs.RunTaskInput
	}
}