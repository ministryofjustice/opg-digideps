package clamd

import (
	"net"
	"testing"
)

func Test_newConnection(t *testing.T) {
	tests := []struct {
		name         string
		port         string
		expectingErr bool
	}{
		{
			name:         "Sucessful connection",
			port:         "9879",
			expectingErr: false,
		},
		{
			name:         "Unsucessful connection",
			port:         "9878",
			expectingErr: true,
		},
	}

	for _, tc := range tests {
		t.Run(tc.name, func(tt *testing.T) {
			ln, _ := net.Listen("tcp", "127.0.0.1:9879")
			defer ln.Close()
			c := NewClamd("tcp://127.0.0.1:" + tc.port)
			_, err := c.newConnection()
			errExist := err != nil
			if tc.expectingErr != errExist {
				tt.Errorf("Error expectation not met, want %v, got %v", tc.expectingErr, errExist)
			}

		})
	}
}
