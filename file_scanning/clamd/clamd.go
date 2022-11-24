package clamd

import (
	"errors"
	"fmt"
	"io"
	"net/url"
	"strings"
)

const (
	RES_OK          = "OK"
	RES_FOUND       = "FOUND"
	RES_ERROR       = "ERROR"
	RES_PARSE_ERROR = "PARSE ERROR"
)

// The clam struct that the various functions we need are attached to
type Clamd struct {
	address string
}

// Various statistics available from clamav
type Stats struct {
	Pools    string
	State    string
	Threads  string
	Memstats string
	Queue    string
}

// Output of the scan operation
type ScanResult struct {
	Raw         string
	Description string
	Path        string
	Hash        string
	Size        int
	Status      string
}

var EICAR = []byte(`X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*`)

// newConnection creates a new connection to ClamD
func (c *Clamd) newConnection() (conn *CLAMDConn, err error) {

	var u *url.URL

	if u, err = url.Parse(c.address); err != nil {
		return
	}

	conn, err = newCLAMDTcpConn(u.Host)

	return
}

// simpleCommand is a genric wrapper to issue commands to ClamD
func (c *Clamd) simpleCommand(command string) (chan *ScanResult, error) {
	conn, err := c.newConnection()
	if err != nil {
		return nil, err
	}

	err = conn.sendCommand(command)
	if err != nil {
		return nil, err
	}

	ch, wg, err := conn.readResponse()

	go func() {
		wg.Wait()
		conn.Close()
	}()

	return ch, err
}

// Ping checks the daemon's state (should reply with PONG).
func (c *Clamd) Ping() error {
	ch, err := c.simpleCommand("PING")
	if err != nil {
		return err
	}

	select {
	case s := (<-ch):
		switch s.Raw {
		case "PONG":
			return nil
		default:
			return errors.New(fmt.Sprintf("Invalid response, got %s.", s.Status))
		}
	}
}

// Version prints program and database versions.
func (c *Clamd) Version() (chan *ScanResult, error) {
	dataArrays, err := c.simpleCommand("VERSION")
	return dataArrays, err
}

// Stats provides statistics about the scan queue, contents of scan
// queue, and memory usage. The exact reply format is subject to changes in future releases.
func (c *Clamd) Stats() (*Stats, error) {
	ch, err := c.simpleCommand("STATS")
	if err != nil {
		return nil, err
	}

	stats := &Stats{}

	for s := range ch {
		switch {
		case strings.HasPrefix(s.Raw, "POOLS"):
			stats.Pools = strings.Trim(s.Raw[6:], " ")
		case strings.HasPrefix(s.Raw, "STATE"):
			stats.State = s.Raw
		case strings.HasPrefix(s.Raw, "THREADS"):
			stats.Threads = s.Raw
		case strings.HasPrefix(s.Raw, "MEMSTATS"):
			stats.Memstats = s.Raw
		case strings.HasPrefix(s.Raw, "END"):
		default:
			return nil, fmt.Errorf("invalid response, got %v", s)
		}
	}

	return stats, nil
}

// Reload reloads the databases.
func (c *Clamd) Reload() error {
	ch, err := c.simpleCommand("RELOAD")
	if err != nil {
		return err
	}

	select {
	case s := (<-ch):
		switch s.Raw {
		case "RELOADING":
			return nil
		default:
			return errors.New(fmt.Sprintf("Invalid response, got %s.", s.Status))
		}
	}
}

// Shutdown shuts down the clamav DB
func (c *Clamd) Shutdown() error {
	_, err := c.simpleCommand("SHUTDOWN")
	if err != nil {
		return err
	}

	return err
}

/*
ScanStream scans a stream of data. The stream is sent to clamd in chunks, after INSTREAM,
on the same socket on which the command was sent. This avoids the overhead
of establishing new TCP connections and problems with NAT. The format of the
chunk is: <length><data> where <length> is the size of the following data in
bytes expressed as a 4 byte unsigned integer in network byte order and <data> is
the actual chunk. Streaming is terminated by sending a zero-length chunk. Note:
do not exceed StreamMaxLength as defined in clamd.conf, otherwise clamd will
reply with INSTREAM size limit exceeded and close the connection
*/
func (c *Clamd) ScanStream(r io.Reader, abort chan bool) (chan *ScanResult, error) {
	conn, err := c.newConnection()
	if err != nil {
		return nil, err
	}

	go func() {
		for {
			_, allowRunning := <-abort
			if !allowRunning {
				break
			}
		}
		conn.Close()
	}()

	conn.sendCommand("INSTREAM")

	for {
		buf := make([]byte, CHUNK_SIZE)

		nr, err := r.Read(buf)
		if nr > 0 {
			conn.sendChunk(buf[0:nr])
		}

		if err != nil {
			break
		}

	}

	err = conn.sendEOF()
	if err != nil {
		return nil, err
	}

	ch, wg, err := conn.readResponse()

	go func() {
		wg.Wait()
		conn.Close()
	}()

	return ch, nil
}

// NewClamd initialises a Clamd object
func NewClamd(address string) *Clamd {
	clamd := &Clamd{address: address}
	return clamd
}
