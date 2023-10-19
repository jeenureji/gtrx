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
 * Servlet implementation class GetGeneDiseases
 */
@WebServlet(description = "Given a geneid or gene symbol, this endpoint will return all diseases related to the gene", urlPatterns = { "/api/gene/diseases" })
public class GetGeneDiseases extends HttpServlet {
	private static final long serialVersionUID = 1L;
       
    /**
     * @see HttpServlet#HttpServlet()
     */
    public GetGeneDiseases() {
        super();
        // TODO Auto-generated constructor stub
    }

	/**
	 * @see HttpServlet#doGet(HttpServletRequest request, HttpServletResponse response)
	 */
	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
		// TODO Auto-generated method stub
		String geneSymbol = request.getParameter("geneSymbol");
		
		if (geneSymbol == null) {
			JSONObject errorMsg = new JSONObject();
			errorMsg.put("success", false);
			errorMsg.put("msg_header", "Diseases for gene");
			errorMsg.put("msg_body", "geneSymbol parameter missing");
			response.setContentType("application/json");
			response.getWriter().append(errorMsg.toString());
			return;
		}
		
		List<Disease> diseases = DiseaseDAO.getDiseasesByGeneSymbol(geneSymbol);
		JSONArray result = new JSONArray();
		Iterator <Disease>diseasesIterator = diseases.iterator();
		while (diseasesIterator.hasNext()) {
			Disease disease = diseasesIterator.next();
			JSONObject diseaseJSON = new JSONObject();
			diseaseJSON.put("conditionId", disease.getId());
			diseaseJSON.put("recordId", disease.getRecordId());
			
			diseaseJSON.put("conditionName", disease.getConditionName());
			diseaseJSON.put("conditionNameAbbreviation", disease.getConditionNameAbbreviation());
			
			String tentativeClinicalSummary = disease.getRcigmClinicalSummary2().trim();
			String summaryTitle = "<div class=\"disease_name_summary\">" + disease.getConditionName() + "</div>";
			diseaseJSON.put("clinicalDescriptionSummaryTitle", summaryTitle);
			if (tentativeClinicalSummary.equals("")) {
				diseaseJSON.put("clinicalDescription", disease.getRcigmClinicalSummary());
			}
			else {
				diseaseJSON.put("clinicalDescription", tentativeClinicalSummary);
			}
			
			result.put(diseaseJSON);
			
		}
		JSONObject successMsg = new JSONObject();
		successMsg.put("success", true);
		successMsg.put("msg_header", "Get disease gene symbol");
		successMsg.put("msg_body", result);
		response.setContentType("application/json");
		response.getWriter().append(successMsg.toString());
	}

}
