package com.ranchobiosciences.radys.gtrx.utilities;

import org.hibernate.SessionFactory;
import org.hibernate.cfg.Configuration;

public final class HibernateUtilities {
	private static Configuration cfg = new Configuration().configure("hibernate.cfg.xml");
	private static SessionFactory factory = cfg.buildSessionFactory();
	private HibernateUtilities (){
		
	}
	public static SessionFactory getSessionFactory(){
		return factory;
	}
}
