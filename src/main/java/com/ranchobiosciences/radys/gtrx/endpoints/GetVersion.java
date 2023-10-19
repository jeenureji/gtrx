package com.ranchobiosciences.radys.gtrx.endpoints;

import com.ranchobiosciences.radys.gtrx.utilities.GeneralUtilities;
import org.json.JSONObject;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;


@WebServlet(description = "This endpoint will return version", urlPatterns = { "/api/about" })
public class GetVersion extends HttpServlet {

   public GetVersion(){
   }
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws IOException {
      String backend_version  =  GeneralUtilities.getProperty("backend_version");
      String frontend_version = GeneralUtilities.getProperty("frontend_version");
      String data_version = GeneralUtilities.getDataProperty("data_version");
      JSONObject successMsg = new JSONObject();
      JSONObject result = new JSONObject();
      result.put("Backend_Version",backend_version );
      result.put("Front_End_Version", frontend_version);
      result.put("Data_Version", data_version);
      successMsg.put("success", true);
      successMsg.put("msg_header", "Get version");
      successMsg.put("msg_body", result);
      response.setContentType("application/json");
      response.getWriter().append(successMsg.toString());
    }

}
