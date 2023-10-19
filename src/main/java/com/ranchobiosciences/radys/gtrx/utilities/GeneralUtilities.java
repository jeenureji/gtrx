package com.ranchobiosciences.radys.gtrx.utilities;

import java.io.File;
import java.io.FileInputStream;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;
import java.util.Properties;

import org.apache.commons.lang3.StringUtils;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.json.JSONArray;
import org.json.JSONObject;

import com.ranchobiosciences.radys.gtrx.persistence.Disease;

public class GeneralUtilities {
	static Logger logger =   LogManager.getLogger("com.ranchobiosciences.chdidispatch");
	public static String getProperty(String propertyName){
		Properties prop = new Properties();
		InputStream input = null;

		try {
			input = Thread.currentThread().getContextClassLoader().getResourceAsStream("/RadysGtrx.properties");
			prop.load(input);
			//logger.debug("PARAMTER VALUE FOR " + propertyName + " IS "+prop.getProperty(propertyName));
			return prop.getProperty(propertyName);
		} catch (Exception e){
			logger.error("error loading from properties file " + e.getMessage());
			return null;
		}

	}
	
	public static String getDataProperty(String dataPropertyName){
		Properties prop = new Properties();
		InputStream input = null;
		String dataPropertiesPath = getProperty("data_properties_path");
		if (dataPropertiesPath == null) {
			return getProperty("data_version");
		}
		try {
			File initialFile = new File(dataPropertiesPath);
			
			if(!initialFile.exists()) { 
				return getProperty("data_version");
			}
		    input = new FileInputStream(initialFile);
			prop.load(input);
			//logger.debug("PARAMTER VALUE FOR " + propertyName + " IS "+prop.getProperty(propertyName));
			return prop.getProperty(dataPropertyName);
		} catch (Exception e){
			logger.error("error loading from properties file " + e.getMessage());
			return e.getMessage();
		}

	}
	
	public static Integer findItemIndexInArray(String[] itemArray, String item) {
		for (int i=0; i<itemArray.length; i++) {
			if (item.toLowerCase().equals(itemArray[i].toLowerCase())) {
				return i;
			}
		}
		
		return -1;
	}
	
	public static String clearStringIfContains (String str, String[] blackList) {
		for (int i=0; i<blackList.length; i++){
			if (str.toLowerCase().startsWith(blackList[i].toLowerCase())){
				return "";
			}
		}
		return str;
	}
	
	private static JSONObject clearJSONObjectAttributeIfContains(JSONObject jsonObj, String jsonObjAttr, String[] blackList) {
		JSONObject result = new JSONObject(jsonObj.toString());
		for (int i=0; i<blackList.length; i++){
			if (result.getString(jsonObjAttr).toLowerCase().startsWith(blackList[i].toLowerCase())){
				result.put(jsonObjAttr, "");
				return result;
			}
		}
		return result;
		
	}
	
	public static JSONArray clearJSONObjectsAttributeIfContains(JSONArray jsonObjects, String jsonObjAttr, String[] blackList) {
		JSONArray result = new JSONArray();
		for (int i=0; i<jsonObjects.length(); i++){
			result.put(clearJSONObjectAttributeIfContains(jsonObjects.getJSONObject(i), jsonObjAttr, blackList));
		}
		return result;
		
	}
	
	public static String generateInverseAgeUseIntString(String ageUseInt) {
		String[] ageUseIntArray = ageUseInt.split(",");
		String inappropriateAgeString = "";
		
		if (GeneralUtilities.findItemIndexInArray(ageUseIntArray, "Neonate") == -1) {
			if (inappropriateAgeString.equals("")) {
				inappropriateAgeString = inappropriateAgeString + "Neonates under 29 days old ";
			}
			else {
				inappropriateAgeString = inappropriateAgeString + "or Neonates under 29 days old ";
			}
		}
		
		if (GeneralUtilities.findItemIndexInArray(ageUseIntArray, "Infant") == -1) {
			if (inappropriateAgeString.equals("")) {
				inappropriateAgeString = inappropriateAgeString + "Infants under 24 months old ";
			}
			else {
				inappropriateAgeString = inappropriateAgeString + "or Infants under 24 months old ";
			}
		}
		
		if (GeneralUtilities.findItemIndexInArray(ageUseIntArray, "Child") == -1) {
			if (inappropriateAgeString.equals("")) {
				inappropriateAgeString = inappropriateAgeString + "Children over 24 months old ";
			}
			else {
				inappropriateAgeString = inappropriateAgeString + "or Children over 24 months old ";
			}
		}
		if (inappropriateAgeString.equals("")) {
			return "";
		}
		else {
			return "Unsuitable for " + inappropriateAgeString.trim();
		}
		
	}
	
	public static List<Disease> removeDupliateDiseasesUsingField(List<Disease> diseases){
		HashMap<Integer, Disease> diseasesHashMap = new HashMap<Integer, Disease>();
		Iterator<Disease> diseasesIterator = diseases.iterator();
		
		while (diseasesIterator.hasNext()) {
			Disease disease = diseasesIterator.next();
			if (disease.getId()!=null) {
				diseasesHashMap.put(disease.getId(), disease);
			}
		}
		
		ArrayList<Disease> result = new ArrayList<Disease>();
		for (Map.Entry<Integer, Disease> diseaseEntry : diseasesHashMap.entrySet()) {
			result.add(diseaseEntry.getValue());
		}
		
		return result;
		
	}
	
	public static String generateStandardIntTimeframeString(String unprocessedTimelineString) {
		String result = "";
		Boolean hoursInterventionTimeframeFlag = false;
		Boolean daysOrWeeksInterventionTimeframeFlag = false;
		Boolean yearsInterventionTimeframeFlag = false;
		
		String[] intTimelineArray = unprocessedTimelineString.split(",");
		
		for (int i=0; i<intTimelineArray.length; i++) {
			if (intTimelineArray[i].toLowerCase().equals("hours")) {
				hoursInterventionTimeframeFlag = true;
			}
			else if (intTimelineArray[i].toLowerCase().equals("days or weeks")) {
				daysOrWeeksInterventionTimeframeFlag = true;
			}
			else if (intTimelineArray[i].toLowerCase().equals("years")) {
				yearsInterventionTimeframeFlag = true;
			}
			
			
		}
		
		if (hoursInterventionTimeframeFlag) {
			result = result + "hours,";
		}
		if (daysOrWeeksInterventionTimeframeFlag) {
			result = result + "daysOrWeeks,";
		}
		if (yearsInterventionTimeframeFlag) {
			result = result + "years,";
		}
		
		return StringUtils.chop(result);
		
		
	}

}
