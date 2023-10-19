package com.ranchobiosciences.radys.gtrx.endpoints;

import java.io.IOException;
import java.util.Iterator;
import java.util.List;
import java.util.TreeMap;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.commons.lang3.math.NumberUtils;
import org.json.JSONArray;
import org.json.JSONObject;

import com.ranchobiosciences.radys.gtrx.dao.DiseaseDAO;
import com.ranchobiosciences.radys.gtrx.persistence.Disease;
import com.ranchobiosciences.radys.gtrx.utilities.GeneralUtilities;

/**
 * Servlet implementation class GetDiseaseById
 */
@WebServlet(description = "Given a disease id or record_id this endpoint will return all information for the disease", urlPatterns = { "/api/disease/id" })
public class GetDiseaseById extends HttpServlet {
	private static final long serialVersionUID = 1L;
       
    /**
     * @see HttpServlet#HttpServlet()
     */
    public GetDiseaseById() {
        super();
        // TODO Auto-generated constructor stub
    }

	/**
	 * @see HttpServlet#doGet(HttpServletRequest request, HttpServletResponse response)
	 */
	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
		// TODO Auto-generated method stub
		String id = request.getParameter("id");
		
		if (id == null) {
			JSONObject errorMsg = new JSONObject();
			errorMsg.put("success", false);
			errorMsg.put("msg_header", "Diseases by id");
			errorMsg.put("msg_body", "id parameter missing");
			response.setContentType("application/json");
			response.getWriter().append(errorMsg.toString());
			return;
		}
		String[] linkBlackList = {"https:\\/\\/redcap.radygenomiclab.com"};
		
		List<Disease> diseases = DiseaseDAO.getDiseasesById(id);
		JSONArray result = new JSONArray();
		Iterator <Disease>diseasesIterator = diseases.iterator();
		while (diseasesIterator.hasNext()) {
			Disease disease = diseasesIterator.next();
			JSONObject diseaseJSON = new JSONObject();
			diseaseJSON.put("id", disease.getId());
			diseaseJSON.put("recordId", disease.getRecordId());
			diseaseJSON.put("nordLink", GeneralUtilities.clearStringIfContains(disease.getNordLink(), linkBlackList));
			diseaseJSON.put("ghrLink", GeneralUtilities.clearStringIfContains(disease.getIncidenceLink(), linkBlackList));
			String[] ghrMenuLinkArray = disease.getIncidenceLink().split("#");
			String ghrMenuLink = disease.getIncidenceLink();
			if (ghrMenuLinkArray.length>=1) {
				ghrMenuLink = ghrMenuLinkArray[0];
			}
			diseaseJSON.put("ghrMenuLink", GeneralUtilities.clearStringIfContains(ghrMenuLink, linkBlackList));
			String tentativeFreqPerBirth = disease.getFreqPerBirth2();
			if (tentativeFreqPerBirth.trim().equals("")) {
				tentativeFreqPerBirth = disease.getFreqPerBirth();
			}
			diseaseJSON.put("ghrDescription", tentativeFreqPerBirth);
			diseaseJSON.put("geneLocation", disease.getGeneLocation());
			diseaseJSON.put("geneName", disease.getGeneName());
			diseaseJSON.put("sequenceViewerLink", GeneralUtilities.clearStringIfContains(disease.getSequnceViewerLink(), linkBlackList));
			diseaseJSON.put("ncbiGeneLink", GeneralUtilities.clearStringIfContains(disease.getNcbiGeneLink(), linkBlackList));
			diseaseJSON.put("geneSymbol", disease.getGeneSymbol());
			String tentativeClinicalSummary = disease.getRcigmClinicalSummary2().trim();
			String summaryTitle = "<div class=\"disease_name_summary\">" + disease.getConditionName() + "</div>";
			if (tentativeClinicalSummary.equals("")) {
				diseaseJSON.put("clinicalDescription", summaryTitle + disease.getRcigmClinicalSummary());
			}
			else {
				diseaseJSON.put("clinicalDescription", summaryTitle + tentativeClinicalSummary);
			}
			
			if (disease.getHpoData() == null) {
				diseaseJSON.put("hpoData", new JSONArray ());
			}
			else {
				diseaseJSON.put("hpoData", new JSONArray (disease.getHpoData()));
			}
			
			
			diseaseJSON.put("collapseGroupNumber", disease.getCollapseGroupNumber());
			diseaseJSON.put("emergencyNoteYn", disease.getEmergencyNoteYn());
			diseaseJSON.put("emergencyNote", disease.getEmergencyNote());
			diseaseJSON.put("group1Diseases", disease.getGroup1Diseases());
			diseaseJSON.put("group1DiseasesAbbreviation", disease.getGroup1DiseasesAbbreviation());
			diseaseJSON.put("group2Diseases", disease.getGroup2Diseases());
			diseaseJSON.put("group2DiseasesAbbreviation", disease.getGroup2DiseasesAbbreviation());
			diseaseJSON.put("group3Diseases", disease.getGroup3Diseases());
			diseaseJSON.put("group3DiseasesAbbreviation", disease.getGroup3DiseasesAbbreviation());
			String tentativeConditionName = disease.getReviewDxName2().trim();
			if (tentativeConditionName.equals("")) {
				diseaseJSON.put("conditionName", disease.getConditionName());
			}
			else {
				diseaseJSON.put("conditionName", tentativeConditionName);
			}
			
			diseaseJSON.put("conditionNameAbbreviation", disease.getConditionNameAbbreviation());
			diseaseJSON.put("conditionName1", disease.getConditionName1());
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
			diseaseJSON.put("conditionName10Abbreviation", disease.getConditionName10Abbreviation());
			diseaseJSON.put("dbHgncGeneId", disease.getDbHgncGeneId());
			diseaseJSON.put("dbHgncGeneSymbol", disease.getDbHgncGeneSymbol());
			String patternOfInheritance = disease.getPatternOfInheritance2();
			if (patternOfInheritance.trim().equals("")) {
				patternOfInheritance = disease.getPatternOfInheritance();
			}
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
			diseaseJSON.put("subspecialist", disease.getSubspecialist().replaceAll("(?i)(?:^other\\s*$|^other\\s*,\\s*(?:\\s*other\\s*,?$)*|other\\s*,\\s*|\\s*,\\s*other\\s*$)", ""));
			diseaseJSON.put("otherSubspecialist", disease.getOtherSubspecialist().replaceAll("(?i)(?:^other\\s*$|^other\\s*,\\s*(?:\\s*other\\s*,?$)*|other\\s*,\\s*|\\s*,\\s*other\\s*$)", ""));
			diseaseJSON.put("split1Yn", disease.getSplit1Yn());
			diseaseJSON.put("dxSubcat1", disease.getDxSubcat1());
			diseaseJSON.put("dxSubcat2", disease.getDxSubcat2());
			
			JSONArray unprocessedInterventionData = new JSONArray();
			if (disease.getInterventionData() != null) {
				unprocessedInterventionData = new JSONArray (disease.getInterventionData());
			}
			JSONArray processedInterventionData =  new JSONArray();
			JSONArray nonIntPriorityClassInterventions = new JSONArray();
			TreeMap<Integer, JSONArray> priorityClassSeparatedInterventions = new TreeMap<Integer, JSONArray>();
			/*HashMap<String, JSONArray> timeFrameSeparatedInterventions = new HashMap<String, JSONArray>();
			timeFrameSeparatedInterventions.put("hours", new JSONArray());
			timeFrameSeparatedInterventions.put("daysOrWeeks", new JSONArray());
			timeFrameSeparatedInterventions.put("years", new JSONArray());
			timeFrameSeparatedInterventions.put("hours,daysOrWeeks", new JSONArray());
			timeFrameSeparatedInterventions.put("hours,years", new JSONArray());
			timeFrameSeparatedInterventions.put("daysOrWeeks,years", new JSONArray());
			timeFrameSeparatedInterventions.put("hours,daysOrWeeks,years", new JSONArray());
			timeFrameSeparatedInterventions.put("other", new JSONArray());*/
			
			for (int i=0; i<unprocessedInterventionData.length(); i++) {
				JSONObject interventionDatum = unprocessedInterventionData.getJSONObject(i);
				Integer redcap_dump_index = interventionDatum.getInt("redcap_dump_index");
				if ((interventionDatum.has("use_group") && interventionDatum.getString("use_group").toLowerCase().startsWith("retain")) || (redcap_dump_index>=31 && redcap_dump_index<=35 && interventionDatum.has("add_int_description") && !interventionDatum.getString("add_int_description").trim().equals(""))) {
					if (redcap_dump_index>=31 && redcap_dump_index<=35 && interventionDatum.has("add_int_description")) {
						interventionDatum.put("intervention_group", interventionDatum.get("add_int_description"));
					}
					
					
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
					
					if (interventionDatum.has("priority_class_drug")) {
						String interventionDatumPriority = interventionDatum.getString("priority_class_drug");



						if (NumberUtils.isCreatable(interventionDatumPriority)) {
							Integer interventionDatumPriorityInt = Integer.parseInt(interventionDatumPriority);
							if (!priorityClassSeparatedInterventions.containsKey(interventionDatumPriorityInt)) {
								priorityClassSeparatedInterventions.put(interventionDatumPriorityInt, new JSONArray());
							}
							priorityClassSeparatedInterventions.get(interventionDatumPriorityInt).put(interventionDatum);
						}
						else {
							nonIntPriorityClassInterventions.put(interventionDatum);
						}
					}
					else {
						nonIntPriorityClassInterventions.put(interventionDatum);
					}
					//String interventionTimelineString = GeneralUtilities.generateStandardIntTimeframeString(interventionDatum.getString("timeframe_int"));
					//System.out.println(interventionTimelineString);
					
					/*if (interventionTimelineString.trim().equals("")){
						timeFrameSeparatedInterventions.get("other").put(interventionDatum);
					}
					else {
						timeFrameSeparatedInterventions.get(interventionTimelineString).put(interventionDatum);
					}*/
				}
			}
			
			for (Integer priorityClass : priorityClassSeparatedInterventions.keySet()) {
				for (int z=0; z<priorityClassSeparatedInterventions.get(priorityClass).length(); z++) {
					processedInterventionData.put(priorityClassSeparatedInterventions.get(priorityClass).get(z));
				}
			}
			
			for (int z=0; z<nonIntPriorityClassInterventions.length(); z++) {
				processedInterventionData.put(nonIntPriorityClassInterventions.get(z));
			}
			
			//System.out.println(timeFrameSeparatedInterventions.toString());
			
			/*JSONArray hoursInterventions = timeFrameSeparatedInterventions.get("hours");
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
			}*/
			
			diseaseJSON.put("interventionData", GeneralUtilities.clearJSONObjectsAttributeIfContains(processedInterventionData, "int_link", linkBlackList));
			if (disease.getDbOmimDx() == null) {
				diseaseJSON.put("dbOmixDx", new JSONArray ());
			}
			else {
				JSONArray dbOmimDx = new JSONArray (disease.getDbOmimDx());
				diseaseJSON.put("dbOmixDx", GeneralUtilities.clearJSONObjectsAttributeIfContains(dbOmimDx, "db_omim_link", linkBlackList));
			}
			
			if (disease.getDbOrphanetDx() == null) {
				diseaseJSON.put("dbOrphanetDx", new JSONArray ());
			}
			else {
				JSONArray dbOrphanetDx = new JSONArray (disease.getDbOrphanetDx());
				diseaseJSON.put("dbOrphanetDx", GeneralUtilities.clearJSONObjectsAttributeIfContains(dbOrphanetDx, "db_orphanet_dx_link", linkBlackList));
			}
			
			
			if (disease.getDbGeneReviews() == null) {
				diseaseJSON.put("dbGeneReviews", new JSONArray ());
			}
			else {
				JSONArray dbGeneReviews = new JSONArray (disease.getDbGeneReviews());
				diseaseJSON.put("dbGeneReviews", GeneralUtilities.clearJSONObjectsAttributeIfContains(dbGeneReviews, "db_genereviews_link", linkBlackList));
			}
			
			if (disease.getDbGhr() == null) {
				diseaseJSON.put("dbGhr", new JSONArray ());
			}
			else {
				JSONArray dbGhr = new JSONArray (disease.getDbGhr());
				diseaseJSON.put("dbGhr", GeneralUtilities.clearJSONObjectsAttributeIfContains(dbGhr, "db_ghr_link", linkBlackList));
			}
			
			if (disease.getDbGardDisease() == null) {
				diseaseJSON.put("gardDisease", new JSONArray ());
			}
			else {
				JSONArray gardDiseases = new JSONArray (disease.getDbGardDisease());
				diseaseJSON.put("gardDisease", GeneralUtilities.clearJSONObjectsAttributeIfContains(gardDiseases, "db_gard_disease_id_link", linkBlackList));
			}
			
			if (disease.getPubmedPublications() == null) {
				diseaseJSON.put("pubmedPublications", new JSONArray ());
			}
			else {
				JSONArray pubmedPublications = new JSONArray (disease.getPubmedPublications());
				diseaseJSON.put("pubmedPublications", GeneralUtilities.clearJSONObjectsAttributeIfContains(pubmedPublications, "pmid_link", linkBlackList));
			}
			
			result.put(diseaseJSON);
			
		}
		JSONObject successMsg = new JSONObject();
		successMsg.put("success", true);
		successMsg.put("msg_header", "Get disease by exact condition name");
		successMsg.put("msg_body", result);
		successMsg.put("resultCount", result.length());
		response.setContentType("application/json");
		response.getWriter().append(successMsg.toString());
	}

	

}
