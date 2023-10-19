package com.ranchobiosciences.radys.gtrx.persistence;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.Table;

@Entity
@Table(name = "gtrx_diseases")
public class Disease {
	private Integer id;
	private String recordId;
	private String nordLink;
	private String incidenceLink;
	private String freqPerBirth;
	private String freqPerBirth2;
	private String geneLocation;
	private String geneName;
	private String sequenceViewerLink;
	private String ncbiGeneLink;
	private String geneSymbol;
	private String rcigmClinicalSummary;
	private String rcigmClinicalSummary2;
	private String hpoData;
	private Integer collapseGroupNumber;
	private String emergencyNoteYn;
	private String emergencyNote;
	private String group1Diseases;
	private String group1DiseasesAbbreviation;
	private String group2Diseases;
	private String group2DiseasesAbbreviation;
	private String group3Diseases;
	private String group3DiseasesAbbreviation;
	private String reviewDxName2;
	private String conditionName;
	private String conditionNameAbbreviation;
	private String conditionName1;
	private String conditionName1Abbreviation;
	private String conditionName2;
	private String conditionName2Abbreviation;
	private String conditionName3;
	private String conditionName3Abbreviation;
	private String conditionName4;
	private String conditionName4Abbreviation;
	private String conditionName5;
	private String conditionName5Abbreviation;
	private String conditionName6;
	private String conditionName6Abbreviation;
	private String conditionName7;
	private String conditionName7Abbreviation;
	private String conditionName8;
	private String conditionName8Abbreviation;
	private String conditionName9;
	private String conditionName9Abbreviation;
	private String conditionName10;
	private String conditionName10Abbreviation;
	private Integer dbHgncGeneId;
	private String dbHgncGeneSymbol;
	private String patternOfInheritance;
	private String patternOfInheritance2;
	private String subspecialistYn;
	private String subspecialist;
	private String otherSubspecialist;
	private String split1Yn;
	private String dxSubcat1;
	private String dxSubcat2;
	private String interventionData;
	private String dbOmimDx;
	private String dbOrphanetDx;
	private String dbGeneReviews;
	private String dbGhr;
	private String dbGardDisease;
	private String pubmedPublications;
	
	
	@Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name="id")
	public Integer getId() {
		return id;
	}
	
	public void setId(Integer id) {
		this.id=id;
	}
	
	
	@Column(name="record_id")
	public String getRecordId() {
		return recordId;
	}
	
	public void setRecordId(String recordId) {
		this.recordId=recordId;
	}
	
	@Column(name="nord_link")
	public String getNordLink() {
		return nordLink;
	}
	
	public void setNordLink(String nordLink) {
		this.nordLink=nordLink;
	}
	
	@Column(name="incidence_link")
	public String getIncidenceLink() {
		return incidenceLink;
	}
	
	public void setIncidenceLink(String incidenceLink) {
		this.incidenceLink=incidenceLink;
	}
	
	@Column(name="freq_per_birth")
	public String getFreqPerBirth() {
		return freqPerBirth;
	}
	
	public void setFreqPerBirth(String freqPerBirth) {
		this.freqPerBirth=freqPerBirth;
	}
	
	@Column(name="freq_per_birth2")
	public String getFreqPerBirth2() {
		return freqPerBirth2;
	}
	
	public void setFreqPerBirth2(String freqPerBirth2) {
		this.freqPerBirth2=freqPerBirth2;
	}
	
	@Column(name="gene_location")
	public String getGeneLocation() {
		return geneLocation;
	}
	
	public void setGeneLocation(String geneLocation) {
		this.geneLocation=geneLocation;
	}
	
	@Column(name="gene_name")
	public String getGeneName() {
		return geneName;
	}
	
	public void setGeneName(String geneName) {
		this.geneName=geneName;
	}
	
	@Column(name="sequence_viewer_link")
	public String getSequnceViewerLink() {
		return sequenceViewerLink;
	}
	
	public void setSequnceViewerLink(String sequenceViewerLink) {
		this.sequenceViewerLink=sequenceViewerLink;
	}
	
	@Column(name="ncbi_gene_link")
	public String getNcbiGeneLink() {
		return ncbiGeneLink;
	}
	
	public void setNcbiGeneLink(String ncbiGeneLink) {
		this.ncbiGeneLink=ncbiGeneLink;
	}
	
	@Column(name="gene_symbol")
	public String getGeneSymbol() {
		return geneSymbol;
	}
	
	public void setGeneSymbol(String geneSymbol) {
		this.geneSymbol=geneSymbol;
	}
	
	@Column(name="rcigm_clinical_summary")
	public String getRcigmClinicalSummary() {
		return rcigmClinicalSummary;
	}
	
	public void setRcigmClinicalSummary(String rcigmClinicalSummary) {
		this.rcigmClinicalSummary=rcigmClinicalSummary;
	}
	
	@Column(name="rcigm_clinical_summary2")
	public String getRcigmClinicalSummary2() {
		return rcigmClinicalSummary2;
	}
	
	public void setRcigmClinicalSummary2(String rcigmClinicalSummary2) {
		this.rcigmClinicalSummary2=rcigmClinicalSummary2;
	}
	
	@Column(name="hpo_data", columnDefinition = "json")
	public String getHpoData() {
		return hpoData;
	}
	
	public void setHpoData(String hpoData) {
		this.hpoData=hpoData;
	}
	
	@Column(name="collapse_group_number")
	public Integer getCollapseGroupNumber() {
		return collapseGroupNumber;
	}
	
	public void setCollapseGroupNumber(Integer collapseGroupNumber) {
		this.collapseGroupNumber=collapseGroupNumber;
	}
	
	@Column(name="emergency_note_yn")
	public String getEmergencyNoteYn() {
		return emergencyNoteYn;
	}
	
	public void setEmergencyNoteYn(String emergencyNoteYn) {
		this.emergencyNoteYn=emergencyNoteYn;
	}
	
	@Column(name="emergency_note")
	public String getEmergencyNote() {
		return emergencyNote;
	}
	
	public void setEmergencyNote(String emergencyNote) {
		this.emergencyNote=emergencyNote;
	}
	
	@Column(name="group_1_diseases")
	public String getGroup1Diseases() {
		return group1Diseases;
	}
	
	public void setGroup1Diseases(String group1Diseases) {
		this.group1Diseases=group1Diseases;
	}
	
	@Column(name="group_1_diseases_abbreviation")
	public String getGroup1DiseasesAbbreviation() {
		return group1DiseasesAbbreviation;
	}
	
	public void setGroup1DiseasesAbbreviation(String group1DiseasesAbbreviation) {
		this.group1DiseasesAbbreviation=group1DiseasesAbbreviation;
	}
	
	@Column(name="group_2_diseases")
	public String getGroup2Diseases() {
		return group2Diseases;
	}
	
	public void setGroup2Diseases(String group2Diseases) {
		this.group2Diseases=group2Diseases;
	}
	
	@Column(name="group_2_diseases_abbreviation")
	public String getGroup2DiseasesAbbreviation() {
		return group2DiseasesAbbreviation;
	}
	
	public void setGroup2DiseasesAbbreviation(String group2DiseasesAbbreviation) {
		this.group2DiseasesAbbreviation=group2DiseasesAbbreviation;
	}
	
	@Column(name="group_3_diseases")
	public String getGroup3Diseases() {
		return group3Diseases;
	}
	
	public void setGroup3Diseases(String group3Diseases) {
		this.group3Diseases=group3Diseases;
	}
	
	@Column(name="group_3_diseases_abbreviation")
	public String getGroup3DiseasesAbbreviation() {
		return group3DiseasesAbbreviation;
	}
	
	public void setGroup3DiseasesAbbreviation(String group3DiseasesAbbreviation) {
		this.group3DiseasesAbbreviation=group3DiseasesAbbreviation;
	}
	
	@Column(name="review_dx_name_2")
	public String getReviewDxName2() {
		return reviewDxName2;
	}
	
	public void setReviewDxName2(String reviewDxName2) {
		this.reviewDxName2=reviewDxName2;
	}
	
	@Column(name="condition_name")
	public String getConditionName() {
		return conditionName;
	}
	
	public void setConditionName(String conditionName) {
		this.conditionName=conditionName;
	}
	
	@Column(name="condition_name_abbreviation")
	public String getConditionNameAbbreviation() {
		return conditionNameAbbreviation;
	}
	
	public void setConditionNameAbbreviation(String conditionNameAbbreviation) {
		this.conditionNameAbbreviation=conditionNameAbbreviation;
	}
	
	@Column(name="condition_name_1")
	public String getConditionName1() {
		return conditionName1;
	}
	
	public void setConditionName1(String conditionName1) {
		this.conditionName1=conditionName1;
	}
	
	@Column(name="condition_name_1_abbreviation")
	public String getConditionName1Abbreviation() {
		return conditionName1Abbreviation;
	}
	
	public void setConditionName1Abbreviation(String conditionName1Abbreviation) {
		this.conditionName1Abbreviation=conditionName1Abbreviation;
	}
	
	@Column(name="condition_name_2")
	public String getConditionName2() {
		return conditionName2;
	}
	
	public void setConditionName2(String conditionName2) {
		this.conditionName2=conditionName2;
	}
	
	@Column(name="condition_name_2_abbreviation")
	public String getConditionName2Abbreviation() {
		return conditionName2Abbreviation;
	}
	
	public void setConditionName2Abbreviation(String conditionName2Abbreviation) {
		this.conditionName2Abbreviation=conditionName2Abbreviation;
	}
	
	@Column(name="condition_name_3")
	public String getConditionName3() {
		return conditionName3;
	}
	
	public void setConditionName3(String conditionName3) {
		this.conditionName3=conditionName3;
	}
	
	@Column(name="condition_name_3_abbreviation")
	public String getConditionName3Abbreviation() {
		return conditionName3Abbreviation;
	}
	
	public void setConditionName3Abbreviation(String conditionName3Abbreviation) {
		this.conditionName3Abbreviation=conditionName3Abbreviation;
	}
	
	@Column(name="condition_name_4")
	public String getConditionName4() {
		return conditionName4;
	}
	
	public void setConditionName4(String conditionName4) {
		this.conditionName4=conditionName4;
	}
	
	@Column(name="condition_name_4_abbreviation")
	public String getConditionName4Abbreviation() {
		return conditionName4Abbreviation;
	}
	
	public void setConditionName4Abbreviation(String conditionName4Abbreviation) {
		this.conditionName4Abbreviation=conditionName4Abbreviation;
	}
	
	@Column(name="condition_name_5")
	public String getConditionName5() {
		return conditionName5;
	}
	
	public void setConditionName5(String conditionName5) {
		this.conditionName5=conditionName5;
	}
	
	@Column(name="condition_name_5_abbreviation")
	public String getConditionName5Abbreviation() {
		return conditionName5Abbreviation;
	}
	
	public void setConditionName5Abbreviation(String conditionName5Abbreviation) {
		this.conditionName5Abbreviation=conditionName5Abbreviation;
	}
	
	@Column(name="condition_name_6")
	public String getConditionName6() {
		return conditionName6;
	}
	
	public void setConditionName6(String conditionName6) {
		this.conditionName6=conditionName6;
	}
	
	@Column(name="condition_name_6_abbreviation")
	public String getConditionName6Abbreviation() {
		return conditionName6Abbreviation;
	}
	
	public void setConditionName6Abbreviation(String conditionName6Abbreviation) {
		this.conditionName6Abbreviation=conditionName6Abbreviation;
	}
	
	@Column(name="condition_name_7")
	public String getConditionName7() {
		return conditionName7;
	}
	
	public void setConditionName7(String conditionName7) {
		this.conditionName7=conditionName7;
	}
	
	@Column(name="condition_name_7_abbreviation")
	public String getConditionName7Abbreviation() {
		return conditionName7Abbreviation;
	}
	
	public void setConditionName7Abbreviation(String conditionName7Abbreviation) {
		this.conditionName7Abbreviation=conditionName7Abbreviation;
	}
	
	@Column(name="condition_name_8")
	public String getConditionName8() {
		return conditionName8;
	}
	
	public void setConditionName8(String conditionName8) {
		this.conditionName8=conditionName8;
	}
	
	@Column(name="condition_name_8_abbreviation")
	public String getConditionName8Abbreviation() {
		return conditionName8Abbreviation;
	}
	
	public void setConditionName8Abbreviation(String conditionName8Abbreviation) {
		this.conditionName8Abbreviation=conditionName8Abbreviation;
	}
	
	@Column(name="condition_name_9")
	public String getConditionName9() {
		return conditionName9;
	}
	
	public void setConditionName9(String conditionName9) {
		this.conditionName9=conditionName9;
	}
	
	@Column(name="condition_name_9_abbreviation")
	public String getConditionName9Abbreviation() {
		return conditionName9Abbreviation;
	}
	
	public void setConditionName9Abbreviation(String conditionName9Abbreviation) {
		this.conditionName9Abbreviation=conditionName9Abbreviation;
	}
	
	@Column(name="condition_name_10")
	public String getConditionName10() {
		return conditionName10;
	}
	
	public void setConditionName10(String conditionName10) {
		this.conditionName10=conditionName10;
	}
	
	@Column(name="condition_name_10_abbreviation")
	public String getConditionName10Abbreviation() {
		return conditionName10Abbreviation;
	}
	
	public void setConditionName10Abbreviation(String conditionName10Abbreviation) {
		this.conditionName10Abbreviation=conditionName10Abbreviation;
	}
	
	@Column(name="db_hgnc_gene_id")
	public Integer getDbHgncGeneId() {
		return dbHgncGeneId;
	}
	
	public void setDbHgncGeneId(Integer dbHgncGeneId) {
		this.dbHgncGeneId=dbHgncGeneId;
	}
	
	@Column(name="db_hgnc_gene_symbol")
	public String getDbHgncGeneSymbol() {
		return dbHgncGeneSymbol;
	}
	
	public void setDbHgncGeneSymbol(String dbHgncGeneSymbol) {
		this.dbHgncGeneSymbol=dbHgncGeneSymbol;
	}
	
	@Column(name="pattern_of_inheritance")
	public String getPatternOfInheritance() {
		return patternOfInheritance;
	}
	
	public void setPatternOfInheritance(String patternOfInheritance) {
		this.patternOfInheritance=patternOfInheritance;
	}
	
	@Column(name="pattern_of_inheritance2")
	public String getPatternOfInheritance2() {
		return patternOfInheritance2;
	}
	
	public void setPatternOfInheritance2(String patternOfInheritance2) {
		this.patternOfInheritance2=patternOfInheritance2;
	}
	
	@Column(name="subspecialist_yn")
	public String getSubspecialistYn() {
		return subspecialistYn;
	}
	
	public void setSubspecialistYn(String subspecialistYn) {
		this.subspecialistYn=subspecialistYn;
	}
	
	@Column(name="subspecialist")
	public String getSubspecialist() {
		return subspecialist;
	}
	
	public void setSubspecialist(String subspecialist) {
		this.subspecialist=subspecialist;
	}
	
	@Column(name="other_subspecialist")
	public String getOtherSubspecialist() {
		return otherSubspecialist;
	}
	
	public void setOtherSubspecialist(String otherSubspecialist) {
		this.otherSubspecialist=otherSubspecialist;
	}
	
	@Column(name="split_1_yn")
	public String getSplit1Yn() {
		return split1Yn;
	}
	
	public void setSplit1Yn(String split1Yn) {
		this.split1Yn=split1Yn;
	}
	
	@Column(name="dx_subcat_1")
	public String getDxSubcat1() {
		return dxSubcat1;
	}
	
	public void setDxSubcat1(String dxSubcat1) {
		this.dxSubcat1=dxSubcat1;
	}
	
	@Column(name="dx_subcat_2")
	public String getDxSubcat2() {
		return dxSubcat2;
	}
	
	public void setDxSubcat2(String dxSubcat2) {
		this.dxSubcat2=dxSubcat2;
	}
	
	
	@Column(name="intervention_data", columnDefinition = "json")
	public String getInterventionData() {
		return this.interventionData;
	}
	
	public void setInterventionData(String interventionData) {
		this.interventionData=interventionData;
	}
	
	
	@Column(name="db_omim_dx", columnDefinition = "json")
	public String getDbOmimDx() {
		return this.dbOmimDx;
	}
	
	public void setDbOmimDx(String dbOmimDx) {
		this.dbOmimDx=dbOmimDx;
	}
	
	
	@Column(name="db_orphanet_dx", columnDefinition = "json")
	public String getDbOrphanetDx() {
		return this.dbOrphanetDx;
	}
	
	public void setDbOrphanetDx(String dbOrphanetDx) {
		this.dbOrphanetDx=dbOrphanetDx;
	}
	
	
	@Column(name="db_gene_reviews", columnDefinition = "json")
	public String getDbGeneReviews() {
		return this.dbGeneReviews;
	}
	
	public void setDbGeneReviews(String dbGeneReviews) {
		this.dbGeneReviews=dbGeneReviews;
	}
	
	
	@Column(name="db_ghr", columnDefinition = "json")
	public String getDbGhr() {
		return this.dbGhr;
	}
	
	public void setDbGhr(String dbGhr) {
		this.dbGhr=dbGhr;
	}
	
	
	@Column(name="db_gard_disease", columnDefinition = "json")
	public String getDbGardDisease() {
		return this.dbGardDisease;
	}
	
	public void setDbGardDisease(String dbGardDisease) {
		this.dbGardDisease=dbGardDisease;
	}
	
	@Column(name="pubmed_publications", columnDefinition = "json")
	public String getPubmedPublications() {
		return this.pubmedPublications;
	}
	
	public void setPubmedPublications(String pubmedPublications) {
		this.pubmedPublications=pubmedPublications;
	}
	
}
