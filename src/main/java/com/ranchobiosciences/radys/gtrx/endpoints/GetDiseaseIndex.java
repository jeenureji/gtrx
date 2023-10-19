package com.ranchobiosciences.radys.gtrx.endpoints;

import java.io.IOException;
import java.util.Iterator;
import java.util.List;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.json.JSONArray;
import org.json.JSONObject;

import com.ranchobiosciences.radys.gtrx.dao.DiseaseDAO;
import com.ranchobiosciences.radys.gtrx.persistence.Disease;

/**
 * Servlet implementation class GetDiseaseByConditionName
 */
@WebServlet(description = "Given a disease conditionName this endpoint will return all information for the disease", urlPatterns = { "/api/disease/index" })
public class GetDiseaseIndex extends HttpServlet {
	private static final long serialVersionUID = 1L;

    /**
     * Default constructor. 
     */
    public GetDiseaseIndex() throws IOException {
		// TODO Auto-generated constructor stub
	}

	/**
	 * @see HttpServlet#doGet(HttpServletRequest request, HttpServletResponse response)
	 */
	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
		// TODO Auto-generated method stub

		List<Disease> diseases = DiseaseDAO.getAllDiseases();
		JSONArray result = new JSONArray();
		Iterator <Disease>diseasesIterator = diseases.iterator();
		while (diseasesIterator.hasNext()) {
			Disease disease = diseasesIterator.next();
			JSONObject diseaseJSON = new JSONObject();
			diseaseJSON.put("conditionId", disease.getId());
			diseaseJSON.put("recordId", disease.getRecordId());
			/*diseaseJSON.put("conditionId", disease.getConditionId());
			diseaseJSON.put("omimId", disease.getOmimId());
			diseaseJSON.put("ghrName", disease.getGhrName());
			diseaseJSON.put("ghrDescription", disease.getGhrDescription());
			diseaseJSON.put("ghrPage", disease.getGhrPage());
			diseaseJSON.put("ghrFrequencyLink", disease.getGhrFrequencyLink());
			diseaseJSON.put("ghrFrequencyText", disease.getGhrFrequencyText());
			
			if (disease.getHpoData() == null) {
				diseaseJSON.put("hpoData", new JSONArray ());
			}
			else {
				diseaseJSON.put("hpoData", new JSONArray (disease.getHpoData()));
			}
			
			diseaseJSON.put("gardLink", disease.getGardLink());
			diseaseJSON.put("condName", disease.getCondName());
			diseaseJSON.put("gardId", disease.getGardId());
			diseaseJSON.put("gardNumber", disease.getGardNumber());
			diseaseJSON.put("mondoLink", disease.getMondoLink());
			diseaseJSON.put("ordoId", disease.getOrdoId());
			diseaseJSON.put("ordoName", disease.getOrdoName());
			diseaseJSON.put("collapseGroupNumber", disease.getCollapseGroupNumber());
			diseaseJSON.put("emergencyNoteYn", disease.getEmergencyNoteYn());
			diseaseJSON.put("emergencyNote", disease.getEmergencyNote());
			diseaseJSON.put("group1Diseases", disease.getGroup1Diseases());
			diseaseJSON.put("group1DiseasesAbbreviation", disease.getGroup1DiseasesAbbreviation());
			diseaseJSON.put("group2Diseases", disease.getGroup2Diseases());
			diseaseJSON.put("group2DiseasesAbbreviation", disease.getGroup2DiseasesAbbreviation());
			diseaseJSON.put("group3Diseases", disease.getGroup3Diseases());
			diseaseJSON.put("group3DiseasesAbbreviation", disease.getGroup3DiseasesAbbreviation());*/
			diseaseJSON.put("conditionName", disease.getConditionName());
			diseaseJSON.put("conditionNameAbbreviation", disease.getConditionNameAbbreviation());
			/*diseaseJSON.put("conditionName1", disease.getConditionName1());
			diseaseJSON.put("conditionName1Abbreviation", disease.getConditionName1Abbreviation());
			diseaseJSON.put("conditionName2", disease.getConditionName2());
			diseaseJSON.put("conditionName2Abbreviation", disease.getConditionName2Abbreviation());
			diseaseJSON.put("conditionName3", disease.getConditionName3());
			diseaseJSON.put("conditionName3Abbreviation", disease.getConditionName3Abbreviation());
			diseaseJSON.put("conditionName4", disease.getConditionName4());
			diseaseJSON.put("conditionName4Abbreviation", disease.getConditionName4Abbreviation());
			diseaseJSON.put("conditionName5", disease.getConditionName5());
			diseaseJSON.put("conditionName5Abbreviation", disease.getConditionName5Abbreviation());
			diseaseJSON.put("conditionName6", disease.getConditionName6());
			diseaseJSON.put("conditionName6Abbreviation", disease.getConditionName6Abbreviation());
			diseaseJSON.put("conditionName7", disease.getConditionName7());
			diseaseJSON.put("conditionName7Abbreviation", disease.getConditionName7Abbreviation());
			diseaseJSON.put("conditionName8", disease.getConditionName8());
			diseaseJSON.put("conditionName8Abbreviation", disease.getConditionName8Abbreviation());
			diseaseJSON.put("conditionName9", disease.getConditionName9());
			diseaseJSON.put("conditionName9Abbreviation", disease.getConditionName9Abbreviation());
			diseaseJSON.put("conditionName10", disease.getConditionName1());
			diseaseJSON.put("conditionName10Abbreviation", disease.getConditionName10Abbreviation());*/
			diseaseJSON.put("dbHgncGeneId", disease.getDbHgncGeneId());
			diseaseJSON.put("dbHgncGeneSymbol", disease.getDbHgncGeneSymbol());
			/*String patternOfInheritance = disease.getPatternOfInheritance();
			diseaseJSON.put("patternOfInheritance", patternOfInheritance);
			if (patternOfInheritance.toLowerCase().equals("autosomal recessive")) {
				diseaseJSON.put("patternOfInheritanceHover", "Autosomal recessive is a type of inheritance of some genetic disorders. <i>Autosomal</i> refers to any of the human chromosomes that is not one of the sex chromosomes, X or Y. <i>Recessive</i> indicates that two copies of the allele are needed to cause the disease.");
			}
			else if (patternOfInheritance.toLowerCase().equals("autosomal dominant")) {
				diseaseJSON.put("patternOfInheritanceHover", "Autosomal dominant is a type of inheritance of some genetic disorders. <i>Autosomal</i> refers to any of the human chromosomes that is not one of the sex chromosomes, X or Y. <i>Dominant</i> indicates that only one copy of the variant is needed to cause the disease.");
			}
			else if (patternOfInheritance.toLowerCase().equals("x-linked")) {
				diseaseJSON.put("patternOfInheritanceHover", "X-linked inheritance refers to a type of inheritance where the disease is inherited on the X chromosome. These conditions are more commonly seen in men, because men carry only one copy of the X chromosome, while women carry two copies.");
			}
			else {
				diseaseJSON.put("patternOfInheritanceHover", "");
			}
			diseaseJSON.put("subspecialistYn", disease.getSubspecialistYn());
			diseaseJSON.put("subspecialist", disease.getSubspecialist());
			diseaseJSON.put("otherSubspecialist", disease.getOtherSubspecialist());
			diseaseJSON.put("split1Yn", disease.getSplit1Yn());
			diseaseJSON.put("dxSubcat1", disease.getDxSubcat1());
			diseaseJSON.put("dxSubcat2", disease.getDxSubcat2());
			
			JSONArray unprocessedInterventionData = new JSONArray();
			if (disease.getInterventionData() != null) {
				unprocessedInterventionData = new JSONArray (disease.getInterventionData());
			}
			JSONArray processedInterventionData =  new JSONArray();
			HashMap<String, JSONArray> timeFrameSeparatedInterventions = new HashMap<String, JSONArray>();
			timeFrameSeparatedInterventions.put("hours", new JSONArray());
			timeFrameSeparatedInterventions.put("daysOrWeeks", new JSONArray());
			timeFrameSeparatedInterventions.put("years", new JSONArray());
			timeFrameSeparatedInterventions.put("hours,daysOrWeeks", new JSONArray());
			timeFrameSeparatedInterventions.put("hours,years", new JSONArray());
			timeFrameSeparatedInterventions.put("daysOrWeeks,years", new JSONArray());
			timeFrameSeparatedInterventions.put("hours,daysOrWeeks,years", new JSONArray());
			timeFrameSeparatedInterventions.put("other", new JSONArray());
			
			for (int i=0; i<unprocessedInterventionData.length(); i++) {
				JSONObject interventionDatum = unprocessedInterventionData.getJSONObject(i);
				if (interventionDatum.has("use_group") && interventionDatum.getString("use_group").toLowerCase().startsWith("retain")) {
					if (interventionDatum.has("age_use_int")) {
						interventionDatum.put("rev_age_use_int", GeneralUtilities.generateInverseAgeUseIntString(interventionDatum.getString("age_use_int")));
					}
					
					if (interventionDatum.has("contra_int")) {
						if (interventionDatum.getString("contra_int").toLowerCase().equals("yes")) {
							interventionDatum.put("contraIndicationTagString", "Contraindications = Yes");
						}
						else if (interventionDatum.getString("contra_int").toLowerCase().equals("no")) { 
							interventionDatum.put("contraIndicationTagString", "Contraindications = No");
						}
						else {
							interventionDatum.put("contraIndicationTagString", "Contraindications = N/A");
						}
					}		
					String interventionTimelineString = GeneralUtilities.generateStandardIntTimeframeString(interventionDatum.getString("timeframe_int"));
					//System.out.println(interventionTimelineString);
					
					if (interventionTimelineString.trim().equals("")){
						timeFrameSeparatedInterventions.get("other").put(interventionDatum);
					}
					else {
						timeFrameSeparatedInterventions.get(interventionTimelineString).put(interventionDatum);
					}
				}
			}
			
			//System.out.println(timeFrameSeparatedInterventions.toString());
			
			JSONArray hoursInterventions = timeFrameSeparatedInterventions.get("hours");
			for (int j=0; j<hoursInterventions.length(); j++) {
				processedInterventionData.put(hoursInterventions.getJSONObject(j));
			}
			
			JSONArray hoursDaysWeeksInterventions = timeFrameSeparatedInterventions.get("hours,daysOrWeeks");
			for (int j=0; j<hoursDaysWeeksInterventions.length(); j++) {
				processedInterventionData.put(hoursDaysWeeksInterventions.getJSONObject(j));
			}
			
			JSONArray hoursDaysWeeksYearsInterventions = timeFrameSeparatedInterventions.get("hours,daysOrWeeks,years");
			for (int j=0; j<hoursDaysWeeksYearsInterventions.length(); j++) {
				processedInterventionData.put(hoursDaysWeeksYearsInterventions.getJSONObject(j));
			}
			
			JSONArray hoursYearsInterventions = timeFrameSeparatedInterventions.get("hours,years");
			for (int j=0; j<hoursYearsInterventions.length(); j++) {
				processedInterventionData.put(hoursYearsInterventions.getJSONObject(j));
			}
			
			JSONArray daysWeeksInterventions = timeFrameSeparatedInterventions.get("daysOrWeeks");
			for (int j=0; j<daysWeeksInterventions.length(); j++) {
				processedInterventionData.put(daysWeeksInterventions.getJSONObject(j));
			}
			
			JSONArray daysWeeksYearsInterventions = timeFrameSeparatedInterventions.get("daysOrWeeks,years");
			for (int j=0; j<daysWeeksYearsInterventions.length(); j++) {
				processedInterventionData.put(daysWeeksYearsInterventions.getJSONObject(j));
			}
			
			JSONArray yearsInterventions = timeFrameSeparatedInterventions.get("years");
			for (int j=0; j<yearsInterventions.length(); j++) {
				processedInterventionData.put(yearsInterventions.getJSONObject(j));
			}
			
			
			JSONArray otherInterventions = timeFrameSeparatedInterventions.get("other");
			for (int j=0; j<otherInterventions.length(); j++) {
				processedInterventionData.put(otherInterventions.getJSONObject(j));
			}
			
			diseaseJSON.put("interventionData", processedInterventionData);
			if (disease.getDbOmimDx() == null) {
				diseaseJSON.put("dbOmixDx", new JSONArray ());
			}
			else {
				diseaseJSON.put("dbOmixDx", new JSONArray (disease.getDbOmimDx()));
			}
			
			if (disease.getDbOrphanetDx() == null) {
				diseaseJSON.put("dbOrphanetDx", new JSONArray ());
			}
			else {
				diseaseJSON.put("dbOrphanetDx", new JSONArray (disease.getDbOrphanetDx()));
			}
			
			
			if (disease.getDbGeneReviews() == null) {
				diseaseJSON.put("dbGeneReviews", new JSONArray ());
			}
			else {
				diseaseJSON.put("dbGeneReviews", new JSONArray (disease.getDbGeneReviews()));
			}
			
			if (disease.getDbGhr() == null) {
				diseaseJSON.put("dbGhr", new JSONArray ());
			}
			else {
				diseaseJSON.put("dbGhr", new JSONArray (disease.getDbGhr()));
			}
			
			if (disease.getDbGardDisease() == null) {
				diseaseJSON.put("gardDisease", new JSONArray ());
			}
			else {
				diseaseJSON.put("gardDisease", new JSONArray (disease.getDbGardDisease()));
			}
			
			if (disease.getPubmedPublications() == null) {
				diseaseJSON.put("pubmedPublications", new JSONArray ());
			}
			else {
				diseaseJSON.put("pubmedPublications", new JSONArray (disease.getPubmedPublications()));
			}*/
			
			result.put(diseaseJSON);
			
		}
		JSONObject successMsg = new JSONObject();
		successMsg.put("success", true);
		successMsg.put("msg_header", "Get disease index");
		successMsg.put("msg_body", result);
		successMsg.put("resultCount", result.length());
		response.setContentType("application/json");
		response.getWriter().append(successMsg.toString());
	}
	
	
	

}
