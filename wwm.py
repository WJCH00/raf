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

class HD44780:
    

    def __init__(self, pin_rs=7, pin_e=8, pins_db=[25, 24, 23, 18]):
        	
        self.pin_rs = pin_rs
        self.pin_e = pin_e
        self.pins_db = pins_db
	#GPIO.cleanup()
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
	#time.sleep(2)
	self.clear()

    def getDistance(self):
	"""Get distance"""
	"""GPIO.output(TRIG,False)"""
	"""time.sleep(2)"""
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
	if distance > 300:
		return 0
	return distance

    def clear(self):
        """ Reset LCD """
        self.cmd(0x33) 
        self.cmd(0x32) 
        self.cmd(0x28) 
        self.cmd(0x0C) 
        self.cmd(0x06) 
        self.cmd(0x01) 
        
    def cmd(self, bits, char_mode=False):
        """ Command to LCD """

        sleep(0.001)
        bits=bin(bits)[2:].zfill(8)
        
        GPIO.output(self.pin_rs, char_mode)

        for pin in self.pins_db:
            GPIO.output(pin, False)

        for i in range(4):
            if bits[i] == "1":
                GPIO.output(self.pins_db[::-1][i], True)

        GPIO.output(self.pin_e, True)
        GPIO.output(self.pin_e, False)

        for pin in self.pins_db:
            GPIO.output(pin, False)

        for i in range(4,8):
            if bits[i] == "1":
                GPIO.output(self.pins_db[::-1][i-4], True)

        GPIO.output(self.pin_e, True)
        GPIO.output(self.pin_e, False)
        
	def insertToDB(distance):

		db = MySQLdb.connect(host="localhost", user="root", passwd="", db="raspberry_mysql")
		cur = db.cursor()

		currentTime = datetime.datetime.now()
		cur.execute("INSERT into distance VALUES('"+str(distance)+"','"+str(currentTime)+")")

		cur.close()
		db.close ()
		
		
    def message(self, text):
        """ Send string to LCD """

        for char in text:
            if char == '\n':
                self.cmd(0xC0) # next line
            else:
                self.cmd(ord(char),True)

if __name__ == '__main__':
	temp = 100
	lcd=HD44780()
	WORK = True;
	while WORK :
		#lcd = HD44780()
		dist1 = lcd.getDistance()
		lcd.insertToDB(dist1)
		sleep(0.1)		
		dist2 = lcd.getDistance()
		sleep(0.1)
		dist3 = lcd.getDistance()
		sleep(0.1)
		dist4 = lcd.getDistance()
		dist = (dist1 + dist2 + dist3 + dist4)/4
		dist = round(dist,2)
		if GPIO.input(BUTT)==0:
			dist = dist - dist%2
		tempMes ="|"
		distTemp=dist
		if distTemp > 30:
			distTemp= 30
		distTemp= distTemp / 2
		while distTemp > 2:
			tempMes = tempMes + "|"
			distTemp= distTemp -1
		#lcd.cmd(0x01)
		#lcd.message(str(dist)+" cm\nLeft: "+str(temp)+" sek")
		#temp = temp - 1
		if dist < 5:
			GPIO.output(BRAKE,True)
			tempMes="  !!! STOP !!!"
		else:
			GPIO.output(BRAKE,False)
		lcd.cmd(0x01)
		sleep(0.01)
		lcd.message("Dis.= "+str(dist)+" cm\n"+tempMes)
		if dist < 10:
			GPIO.output(BUZZ0,True)
			GPIO.output(BUZZ1,False)
			GPIO.output(BUZZ2,False)
		if dist >= 10 and dist < 20:
			GPIO.output(BUZZ0,False)
			GPIO.output(BUZZ1,True)
			GPIO.output(BUZZ2,False)
		if dist >=20 and dist < 30:
			GPIO.output(BUZZ0,False)
			GPIO.output(BUZZ1,False)
			GPIO.output(BUZZ2,True)
		if dist >= 30:
			GPIO.output(BUZZ0,False)
			GPIO.output(BUZZ1,False)
			GPIO.output(BUZZ2,False)	
		#GPIO.output(BUZZ,True)
		sleep(0.1)
		#lcd.clear()
		#lcd.cmd(0x01)

