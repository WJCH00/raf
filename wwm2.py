#!/usr/bin/python
import RPi.GPIO as GPIO
import MySQLdb
import datetime
import time
from time import sleep
TRIG = 20
ECHO = 21
BUZZ0 = 16
BUZZ1 = 12
BUZZ2 = 19
BUTT = 26
BRAKE = 13

class ShopCounter:


    def __init__(self, pin_rs=7, pin_e=8, pins_db=[25, 24, 23, 18]):

        self.pin_rs = pin_rs
        self.pin_e = pin_e
        self.pins_db = pins_db
	GPIO.setwarnings(False)
        GPIO.setmode(GPIO.BCM)
        GPIO.setup(self.pin_e, GPIO.OUT)
        GPIO.setup(self.pin_rs, GPIO.OUT)
        for pin in self.pins_db:
            GPIO.setup(pin, GPIO.OUT)
	GPIO.setup(TRIG,GPIO.OUT)
        GPIO.setup(ECHO,GPIO.IN)
	GPIO.setup(BUZZ0,GPIO.OUT)
	GPIO.setup(BUZZ1,GPIO.OUT)
	GPIO.setup(BUZZ2,GPIO.OUT)
	GPIO.setup(BRAKE,GPIO.OUT)
	GPIO.setup(BUTT,GPIO.IN)
	GPIO.output(TRIG,False)

    def getDistance(self):
	""" get distance """
	GPIO.output(TRIG,True)
	time.sleep(0.00001)
	GPIO.output(TRIG,False)
	while GPIO.input(ECHO)==0:
		pulse_start=time.time()
	while GPIO.input(ECHO)==1:
		pulse_end=time.time()
	pulse_time=pulse_end-pulse_start
	distance=pulse_time * 17150
	distance=round(distance,2)
	
	return distance

    def insertToDB(self, distance):
        """ insert distance and current time to DB """
        db = MySQLdb.connect(host="localhost", user="root", passwd="qwerty", db="raspberry_mysql")
        cur = db.cursor()
        currentTime = datetime.datetime.now()
        query = ("""INSERT INTO distance(distance, date) VALUES(%(distance)s, %(currentTime)s)""")
        data = {
		'distance': distance,
		'currentTime': currentTime,
	}
	cur.execute(query, data)
	db.commit()
	print 'Data inserted ' + str(distance)
        cur.close()
        db.close()

if __name__ == '__main__':
	sc=ShopCounter()
	blockDB = False;
	while True :
		dist1 = sc.getDistance()
		sleep(0.1)
		dist2 = sc.getDistance()
		sleep(0.1)
		dist3 = sc.getDistance()
		sleep(0.1)
		dist4 = sc.getDistance()
		dist = (dist1 + dist2 + dist3 + dist4)/4
		dist = round(dist,2)
		if GPIO.input(BUTT)==0:
			dist = dist - dist%2
		
		print dist

		if dist < 50:
			if blockDB == False:
				sc.insertToDB(dist)
				blockDB = True;
				GPIO.output(BRAKE,True)
				GPIO.output(BUZZ1,True)
				sleep(0.5)
				GPIO.output(BUZZ1,False)
				GPIO.output(BRAKE,False)
		else:
			GPIO.output(BRAKE,False)
			blockDB = False;
		sleep(0.1)

