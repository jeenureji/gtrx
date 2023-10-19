package com.ranchobiosciences.radys.gtrx.dao;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;

import javax.persistence.Query;

import org.apache.commons.lang3.math.NumberUtils;
import org.hibernate.Session;
import org.json.JSONArray;
import org.json.JSONObject;

import com.ranchobiosciences.radys.gtrx.persistence.Disease;
import com.ranchobiosciences.radys.gtrx.utilities.GeneralUtilities;
import com.ranchobiosciences.radys.gtrx.utilities.HibernateUtilities;

public final class DiseaseDAO {
	private DiseaseDAO () {
		
	}
	
	public static List<Disease> getAllDiseases() {
		Session session = HibernateUtilities.getSessionFactory().openSession();
		session.beginTransaction();
		Query query = session.createQuery("from Disease d ORDER BY d.conditionName ASC");
		List<?> diseases = query.getResultList();
		ArrayList<Disease> result = new ArrayList<Disease>();
		Iterator<?> diseaseIterator = diseases.iterator();
		while (diseaseIterator.hasNext()) {
			Disease disease = (Disease)diseaseIterator.next();
			result.add(disease);
		}
		session.close();
		return result;
	}
	
	public static List<Disease> getDiseasesByConditionName(String conditionName) {
		Session session = HibernateUtilities.getSessionFactory().openSession();
		session.beginTransaction();
		Query query = session.createQuery("from Disease where lower(conditionName) = :conditionName");
		query.setParameter("conditionName", conditionName.toLowerCase());
		List<?> diseases = query.getResultList();
		ArrayList<Disease> result = new ArrayList<Disease>();
		Iterator<?> diseaseIterator = diseases.iterator();
		while (diseaseIterator.hasNext()) {
			Disease disease = (Disease)diseaseIterator.next();
			result.add(disease);
		}
		session.close();
		return result;
	}
	
	public static List<Disease> getDiseasesByHgncGeneId(Integer hgncGeneId) {
		Session session = HibernateUtilities.getSessionFactory().openSession();
		session.beginTransaction();
		Query query = session.createQuery("from Disease where hgncGeneId = :hgncGeneId");
		query.setParameter("hgncGeneId", hgncGeneId);
		List<?> diseases = query.getResultList();
		ArrayList<Disease> result = new ArrayList<Disease>();
		Iterator<?> diseaseIterator = diseases.iterator();
		while (diseaseIterator.hasNext()) {
			Disease disease = (Disease)diseaseIterator.next();
			result.add(disease);
		}
		session.close();
		return result;
	}
	
	public static List<Disease> getDiseasesByHgncGeneSymbol(String dbHgncGeneSymbol) {
		Session session = HibernateUtilities.getSessionFactory().openSession();
		session.beginTransaction();
		Query query = session.createQuery("from Disease where lower(dbHgncGeneSymbol) = :dbHgncGeneSymbol");
		query.setParameter("dbHgncGeneSymbol", dbHgncGeneSymbol.toLowerCase());
		List<?> diseases = query.getResultList();
		ArrayList<Disease> result = new ArrayList<Disease>();
		Iterator<?> diseaseIterator = diseases.iterator();
		while (diseaseIterator.hasNext()) {
			Disease disease = (Disease)diseaseIterator.next();
			result.add(disease);
		}
		session.close();
		return result;
	}
	
	public static List<Disease> getDiseasesByHgncGene(String geneData) {
		
		String geneQueryString = "from Disease where ";
		if (NumberUtils.isDigits(geneData)) {
			geneQueryString = geneQueryString + "dbHgncGeneId = :dbHgncGeneId";
		}
		else {
			geneQueryString = geneQueryString + "lower(dbHgncGeneSymbol) = :dbHgncGeneSymbol";
		}
		
		
		Session session = HibernateUtilities.getSessionFactory().openSession();
		session.beginTransaction();
		Query query = session.createQuery(geneQueryString);
		
		if (NumberUtils.isDigits(geneData)) {
			query.setParameter("dbHgncGeneId", Integer.parseInt(geneData));
		}
		else {
			query.setParameter("dbHgncGeneSymbol", geneData.toLowerCase());
		}
		List<?> diseases = query.getResultList();
		ArrayList<Disease> result = new ArrayList<Disease>();
		Iterator<?> diseaseIterator = diseases.iterator();
		while (diseaseIterator.hasNext()) {
			Disease disease = (Disease)diseaseIterator.next();
			result.add(disease);
		}
		session.close();
		return result;
	}
	
	public static List<Disease> getDiseasesById(String id) {
		
		String geneQueryString = "from Disease where ";
		if (NumberUtils.isDigits(id)) {
			geneQueryString = geneQueryString + "id = :id";
		}
		else {
			geneQueryString = geneQueryString + "lower(record_id) = :record_id";
		}
		
		
		Session session = HibernateUtilities.getSessionFactory().openSession();
		session.beginTransaction();
		Query query = session.createQuery(geneQueryString);
		
		if (NumberUtils.isDigits(id)) {
			query.setParameter("id", Integer.parseInt(id));
		}
		else {
			query.setParameter("record_id", id.toLowerCase());
		}
		
		List<?> diseases = query.getResultList();
		ArrayList<Disease> result = new ArrayList<Disease>();
		Iterator<?> diseaseIterator = diseases.iterator();
		while (diseaseIterator.hasNext()) {
			Disease disease = (Disease)diseaseIterator.next();
			result.add(disease);
		}
		session.close();
		return result;
	}
	
	public static List<Disease> getDiseasesByOmimId(String db_omim_id) {
		Session session = HibernateUtilities.getSessionFactory().openSession();
		session.beginTransaction();
		String queryString = "select * from gtrx_schema.gtrx_diseases gd, json_array_elements(gd.db_omim_dx) obj where obj->>'db_omim_id' =:db_omim_id";
		Query query = session.createNativeQuery(queryString, Disease.class);
		query.setParameter("db_omim_id", db_omim_id);
		List<?> diseases = query.getResultList();
		ArrayList<Disease> result = new ArrayList<Disease>();
		Iterator<?> diseaseIterator = diseases.iterator();
		while (diseaseIterator.hasNext()) {
			Disease disease = (Disease)diseaseIterator.next();
			result.add(disease);
		}
		session.close();
		return result;
	}
	
	public static JSONArray getDistinctGenesForAllDiseases() {
		JSONArray result = new JSONArray();
		Session session = HibernateUtilities.getSessionFactory().openSession();
		session.beginTransaction();
		String queryString = "select DISTINCT db_hgnc_gene_symbol from gtrx_schema.gtrx_diseases order by db_hgnc_gene_symbol";
		
		
		Query query = session.createNativeQuery(queryString);
		List<?> queryResult = query.getResultList();
		Iterator<?> queryResultIterator = queryResult.iterator();
		while (queryResultIterator.hasNext()) {
			
			
			result.put(queryResultIterator.next());
		}
		
		return result;
		
	}
	
	public static JSONObject getDistinctGenesForAllDiseases2() {
		JSONObject result = new JSONObject();
		Session session = HibernateUtilities.getSessionFactory().openSession();
		session.beginTransaction();
		String queryString = "select DISTINCT db_hgnc_gene_symbol, condition_name, id from gtrx_schema.gtrx_diseases order by db_hgnc_gene_symbol";
		
		
		Query query = session.createNativeQuery(queryString);
		List<?> queryResult = query.getResultList();
		Iterator<?> queryResultIterator = queryResult.iterator();
		while (queryResultIterator.hasNext()) {
			
			Object[] fieldData = (Object[])queryResultIterator.next();
			String geneSymbol = (String)fieldData[0];
			if (!result.has(geneSymbol)) {
				result.put(geneSymbol, new JSONArray());
			}
			JSONObject diseaseData = new JSONObject();
			if (fieldData[1] != null) {
				diseaseData.put("conditionName", (String)fieldData[1]);
			}
			else {
				diseaseData.put("conditionName", "");
			}
			diseaseData.put("conditionId", (Integer)fieldData[2]);
			
			result.getJSONArray(geneSymbol).put(diseaseData);
				
			
		}
		session.close();
		return result;
		
	}
	
	public static JSONArray getGeneDataByGeneSymbol(String geneSymbol) {
		JSONArray result = new JSONArray();
		Session session = HibernateUtilities.getSessionFactory().openSession();
		session.beginTransaction();
		String queryString = "select DISTINCT db_hgnc_gene_symbol, gene_name, gene_location from gtrx_schema.gtrx_diseases gd WHERE lower(gd.db_hgnc_gene_symbol) = :dbHgncGeneSymbol";
		
		
		Query query = session.createNativeQuery(queryString);
		query.setParameter("dbHgncGeneSymbol", geneSymbol.toLowerCase());
		List<?> queryResult = query.getResultList();
		Iterator<?> queryResultIterator = queryResult.iterator();
		while (queryResultIterator.hasNext()) {
			String returnedGeneSymbol = "";
			String geneName = "";
			String geneLocation = "";
			JSONObject geneData = new JSONObject();
			Object[] fieldData = (Object[])queryResultIterator.next();
			if (fieldData[0]!=null) {
				returnedGeneSymbol = fieldData[0].toString();
			}
			
			if (fieldData[1]!=null) {
				geneName = fieldData[1].toString();
			}
			
			if (fieldData[2]!=null) {
				geneLocation = fieldData[2].toString();
			}
			
			geneData.put("geneSymbol", returnedGeneSymbol);
			geneData.put("geneName", geneName);
			geneData.put("geneLocation", geneLocation);
			
			
			result.put(geneData);
		}
		session.close();
		return result;
		
	}
	
	public static List<Disease> getDiseasesByGeneSymbol(String geneSymbol){
		ArrayList<Disease> result = new ArrayList<Disease>();
		Session session = HibernateUtilities.getSessionFactory().openSession();
		session.beginTransaction();
		String geneQueryString = "from Disease d where lower(dbHgncGeneSymbol) = :dbHgncGeneSymbol ORDER BY d.conditionName ASC";
		Query query = session.createQuery(geneQueryString);
		query.setParameter("dbHgncGeneSymbol", geneSymbol.toLowerCase());
		List<?> diseases = query.getResultList();
		
		Iterator<?> diseaseIterator = diseases.iterator();
		while (diseaseIterator.hasNext()) {
			Disease disease = (Disease)diseaseIterator.next();
			result.add(disease);
		}
		session.close();
		return result;
	}
	
	public static List<Disease> getDiseasesByUnifiedSearch(String unifiedSearchParam){
		Session session = HibernateUtilities.getSessionFactory().openSession();
		session.beginTransaction();
		String recordIdSearchCondString = "lower(record_id) like :unifiedSearchParamRecordId";
		
		String reviewDxName2SearchCondString = "lower(review_dx_name_2) like :unifiedSearchParamCondName";
		
		String conditionNameSearchCondString = "lower(condition_name) like :unifiedSearchParamCondName";
		String conditionNameSearchAbbreviationString = "lower(condition_name_abbreviation) =:unifiedSearchParam";
		
		String conditionName1SearchCondString = "lower(condition_name_1) =:unifiedSearchParam";
		String conditionName1SearchAbbreviationString = "lower(condition_name_1_abbreviation) =:unifiedSearchParam";
		
		String conditionName2SearchCondString = "lower(condition_name_2) =:unifiedSearchParam";
		String conditionName2SearchAbbreviationString = "lower(condition_name_2_abbreviation) =:unifiedSearchParam";
		
		String conditionName3SearchCondString = "lower(condition_name_3) =:unifiedSearchParam";
		String conditionName3SearchAbbreviationString = "lower(condition_name_3_abbreviation) =:unifiedSearchParam";
		
		String conditionName4SearchCondString = "lower(condition_name_4) =:unifiedSearchParam";
		String conditionName4SearchAbbreviationString = "lower(condition_name_4_abbreviation) =:unifiedSearchParam";
		
		String conditionName5SearchCondString = "lower(condition_name_5) =:unifiedSearchParam";
		String conditionName5SearchAbbreviationString = "lower(condition_name_5_abbreviation) =:unifiedSearchParam";
		
		String conditionName6SearchCondString = "lower(condition_name_6) =:unifiedSearchParam";
		String conditionName6SearchAbbreviationString = "lower(condition_name_6_abbreviation) =:unifiedSearchParam";
		
		String conditionName7SearchCondString = "lower(condition_name_7) =:unifiedSearchParam";
		String conditionName7SearchAbbreviationString = "lower(condition_name_7_abbreviation) =:unifiedSearchParam";
		
		String conditionName8SearchCondString = "lower(condition_name_8) =:unifiedSearchParam";
		String conditionName8SearchAbbreviationString = "lower(condition_name_8_abbreviation) =:unifiedSearchParam";
		
		String conditionName9SearchCondString = "lower(condition_name_9) =:unifiedSearchParam";
		String conditionName9SearchAbbreviationString = "lower(condition_name_9_abbreviation) =:unifiedSearchParam";
		
		String conditionName10SearchCondString = "lower(condition_name_10) =:unifiedSearchParam";
		String conditionName10SearchAbbreviationString = "lower(condition_name_10_abbreviation) =:unifiedSearchParam";
		
		
		String hgncGeneIdSearchCondString = "db_hgnc_gene_id =:numUnifiedSearchParam";
		String hgncGeneSymbolSearchCondString = "lower(db_hgnc_gene_symbol) =:unifiedSearchParam";
		
		String omimIdQueryString = "select * from gtrx_schema.gtrx_diseases gd, json_array_elements(gd.db_omim_dx) obj where (lower(obj->>'db_omim_id') =:unifiedSearchParam";
		
		omimIdQueryString = omimIdQueryString + " or " + recordIdSearchCondString;
		
		if (unifiedSearchParam.length()>=3) {
			omimIdQueryString = omimIdQueryString + " or " + reviewDxName2SearchCondString;
			omimIdQueryString = omimIdQueryString + " or " + conditionNameSearchCondString;
		}
		omimIdQueryString = omimIdQueryString + " or " + conditionNameSearchAbbreviationString;
		
		omimIdQueryString = omimIdQueryString + " or " + conditionName1SearchCondString;
		omimIdQueryString = omimIdQueryString + " or " + conditionName1SearchAbbreviationString;
		
		omimIdQueryString = omimIdQueryString + " or " + conditionName2SearchCondString;
		omimIdQueryString = omimIdQueryString + " or " + conditionName2SearchAbbreviationString;
		
		omimIdQueryString = omimIdQueryString + " or " + conditionName3SearchCondString;
		omimIdQueryString = omimIdQueryString + " or " + conditionName3SearchAbbreviationString;
		
		omimIdQueryString = omimIdQueryString + " or " + conditionName4SearchCondString;
		omimIdQueryString = omimIdQueryString + " or " + conditionName4SearchAbbreviationString;
		
		omimIdQueryString = omimIdQueryString + " or " + conditionName5SearchCondString;
		omimIdQueryString = omimIdQueryString + " or " + conditionName5SearchAbbreviationString;
		
		omimIdQueryString = omimIdQueryString + " or " + conditionName6SearchCondString;
		omimIdQueryString = omimIdQueryString + " or " + conditionName6SearchAbbreviationString;
		
		omimIdQueryString = omimIdQueryString + " or " + conditionName7SearchCondString;
		omimIdQueryString = omimIdQueryString + " or " + conditionName7SearchAbbreviationString;
		
		omimIdQueryString = omimIdQueryString + " or " + conditionName8SearchCondString;
		omimIdQueryString = omimIdQueryString + " or " + conditionName8SearchAbbreviationString;
		
		omimIdQueryString = omimIdQueryString + " or " + conditionName9SearchCondString;
		omimIdQueryString = omimIdQueryString + " or " + conditionName9SearchAbbreviationString;
		
		omimIdQueryString = omimIdQueryString + " or " + conditionName10SearchCondString;
		omimIdQueryString = omimIdQueryString + " or " + conditionName10SearchAbbreviationString;
		
		
		Integer numUnifiedSearchParam = null;
		if (NumberUtils.isDigits(unifiedSearchParam)) {
			omimIdQueryString = omimIdQueryString + " or " + hgncGeneIdSearchCondString;
			numUnifiedSearchParam = Integer.parseInt(unifiedSearchParam);
		}
		
		omimIdQueryString = omimIdQueryString + " or " + hgncGeneSymbolSearchCondString;
		
		Query query = session.createNativeQuery(omimIdQueryString +")" , Disease.class);
		query.setParameter("unifiedSearchParamRecordId", "%-" + unifiedSearchParam.toLowerCase());
		query.setParameter("unifiedSearchParam", unifiedSearchParam.toLowerCase());
		if (unifiedSearchParam.length()>=3) {
			query.setParameter("unifiedSearchParamCondName", "%" + unifiedSearchParam.toLowerCase() + "%");
		}
		if (numUnifiedSearchParam!=null) {
			query.setParameter("numUnifiedSearchParam", numUnifiedSearchParam);
		}
		List<?> diseases = query.getResultList();
		ArrayList<Disease> result = new ArrayList<Disease>();
		Iterator<?> diseaseIterator = diseases.iterator();
		while (diseaseIterator.hasNext()) {
			Disease disease = (Disease)diseaseIterator.next();
			result.add(disease);
		}
		
		
		
		String treatmentQueryString = "select * from gtrx_schema.gtrx_diseases gd, json_array_elements(gd.intervention_data) obj where (lower(obj->>'intervention_group') = :unifiedSearchParam ";
		treatmentQueryString = treatmentQueryString + "or  lower(obj->>'intervention_group') like :unifiedSearchParamStart ";
		treatmentQueryString = treatmentQueryString + "or  lower(obj->>'intervention_group') like :unifiedSearchParamMiddle ";
		treatmentQueryString = treatmentQueryString + "or  lower(obj->>'intervention_group') like :unifiedSearchParamEnd ";
		
		treatmentQueryString = treatmentQueryString + " or " + recordIdSearchCondString;
		if (unifiedSearchParam.length()>=3) {
			treatmentQueryString = treatmentQueryString + " or " + conditionNameSearchCondString;
		}
		treatmentQueryString = treatmentQueryString + " or " + conditionNameSearchAbbreviationString;
		
		treatmentQueryString = treatmentQueryString + " or " + conditionName1SearchCondString;
		treatmentQueryString = treatmentQueryString + " or " + conditionName1SearchAbbreviationString;
		
		treatmentQueryString = treatmentQueryString + " or " + conditionName2SearchCondString;
		treatmentQueryString = treatmentQueryString + " or " + conditionName2SearchAbbreviationString;
		
		treatmentQueryString = treatmentQueryString + " or " + conditionName3SearchCondString;
		treatmentQueryString = treatmentQueryString + " or " + conditionName3SearchAbbreviationString;
		
		treatmentQueryString = treatmentQueryString + " or " + conditionName4SearchCondString;
		treatmentQueryString = treatmentQueryString + " or " + conditionName4SearchAbbreviationString;
		
		treatmentQueryString = treatmentQueryString + " or " + conditionName5SearchCondString;
		treatmentQueryString = treatmentQueryString + " or " + conditionName5SearchAbbreviationString;
		
		treatmentQueryString = treatmentQueryString + " or " + conditionName6SearchCondString;
		treatmentQueryString = treatmentQueryString + " or " + conditionName6SearchAbbreviationString;
		
		treatmentQueryString = treatmentQueryString + " or " + conditionName7SearchCondString;
		treatmentQueryString = treatmentQueryString + " or " + conditionName7SearchAbbreviationString;
		
		treatmentQueryString = treatmentQueryString + " or " + conditionName8SearchCondString;
		treatmentQueryString = treatmentQueryString + " or " + conditionName8SearchAbbreviationString;
		
		treatmentQueryString = treatmentQueryString + " or " + conditionName9SearchCondString;
		treatmentQueryString = treatmentQueryString + " or " + conditionName9SearchAbbreviationString;
		
		treatmentQueryString = treatmentQueryString + " or " + conditionName10SearchCondString;
		treatmentQueryString = treatmentQueryString + " or " + conditionName10SearchAbbreviationString;
		
		if (NumberUtils.isDigits(unifiedSearchParam)) {
			treatmentQueryString = treatmentQueryString + " or " + hgncGeneIdSearchCondString;
			numUnifiedSearchParam = Integer.parseInt(unifiedSearchParam);
		}
		
		treatmentQueryString = treatmentQueryString + " or " + hgncGeneSymbolSearchCondString;
		query = session.createNativeQuery(treatmentQueryString + ")", Disease.class);
		query.setParameter("unifiedSearchParamRecordId", "%-" + unifiedSearchParam.toLowerCase());
		query.setParameter("unifiedSearchParam", unifiedSearchParam.toLowerCase());
		if (unifiedSearchParam.length()>=3) {
			query.setParameter("unifiedSearchParamCondName", "%" + unifiedSearchParam.toLowerCase() + "%");
		}
		query.setParameter("unifiedSearchParamStart", unifiedSearchParam.toLowerCase() + ",%");
		query.setParameter("unifiedSearchParamMiddle", "%, " + unifiedSearchParam.toLowerCase() + ",%");
		query.setParameter("unifiedSearchParamEnd", "%, " + unifiedSearchParam.toLowerCase());
		
		if (numUnifiedSearchParam!=null) {
			query.setParameter("numUnifiedSearchParam", numUnifiedSearchParam);
		}
		diseases = query.getResultList();
		
		diseaseIterator = diseases.iterator();
		while (diseaseIterator.hasNext()) {
			Disease disease = (Disease)diseaseIterator.next();
			result.add(disease);
		}
		
		
		
		session.close();
		return GeneralUtilities.removeDupliateDiseasesUsingField(result);
		
	}
	
	
} 
