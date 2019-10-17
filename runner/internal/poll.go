package internal

import (
	"time"
)

type Poll struct {
	Count    int
	Interval int
	Timeout  int
}

func (p *Poll) IsTimedOut() bool {
	return p.Count*p.Interval >= p.Timeout
}

func (p *Poll) Sleep() {
	time.Sleep(time.Duration(p.Interval) * time.Second)
	p.Count++
}