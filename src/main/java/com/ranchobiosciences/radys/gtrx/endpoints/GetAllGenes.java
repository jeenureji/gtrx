package com.ranchobiosciences.radys.gtrx.endpoints;

import java.io.IOException;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.json.JSONArray;
import org.json.JSONObject;

import com.ranchobiosciences.radys.gtrx.dao.DiseaseDAO;

/**
 * Servlet implementation class GetAllGenes
 */
public class GetAllGenes extends HttpServlet {
	private static final long serialVersionUID = 1L;
       
    /**
     * @see HttpServlet#HttpServlet()
     */
    public GetAllGenes() {
        super();
        // TODO Auto-generated constructor stub
    }

	/**
	 * @see HttpServlet#doGet(HttpServletRequest request, HttpServletResponse response)
	 */
	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
		// TODO Auto-generated method stub
		JSONArray genesForAllDiseases = DiseaseDAO.getDistinctGenesForAllDiseases();
		JSONObject successMsg = new JSONObject();
		successMsg.put("success", true);
		successMsg.put("msg_header", "Get all disease genes");
		successMsg.put("msg_body", genesForAllDiseases);
		
		response.setContentType("application/json");
		response.getWriter().append(successMsg.toString());
	}

}
