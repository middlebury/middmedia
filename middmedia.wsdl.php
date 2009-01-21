<?php

define("MYDIR",dirname(__FILE__));

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
	$protocol = 'https';
else
	$protocol = 'http';

define("MYPATH", $protocol."://".$_SERVER['HTTP_HOST'].str_replace(
												"\\", "/", 
												dirname($_SERVER['PHP_SELF'])));
							
print '<'.'?xml version="1.0" encoding="ISO-8859-1"?'.'>';

?>

<definitions
	xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
	xmlns:tns="urn:MiddTube"
	xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
	xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
	xmlns="http://schemas.xmlsoap.org/wsdl/"
	targetNamespace="urn:MiddTube">
	<types>
		<xsd:schema targetNamespace="urn:MiddTube">
			<xsd:import namespace="http://schemas.xmlsoap.org/soap/encoding/" />
			<xsd:import namespace="http://schemas.xmlsoap.org/wsdl/" />
			<xsd:complexType name="video">
				<xsd:sequence>
					<xsd:element name="name" type="xsd:string" />
					<xsd:element name="httpurl" type="xsd:string" />
					<xsd:element name="rtmpurl" type="xsd:string" />
					<xsd:element name="mimetype" type="xsd:string" />
					<xsd:element name="size" type="xsd:positiveInteger" />
					<xsd:element name="date" type="xsd:dateTime" />
					<xsd:element name="creator" type="xsd:string" />
				</xsd:sequence>
			</xsd:complexType>
			<xsd:complexType name="videos">
				<xsd:complexContent>
					<xsd:restriction base="SOAP-ENC:Array">
						<attribute ref="SOAP-ENC:arrayType" wsdl:arrayType="tns:video[]" />
					</xsd:restriction>
				</xsd:complexContent>
			</xsd:complexType>
			<xsd:complexType name="directory">
				<xsd:sequence>
					<xsd:element name="name" type="xsd:string" />
					<xsd:element name="bytesused" type="xsd:positiveInteger" />
					<xsd:element name="bytesavailable" type="xsd:positiveInteger" />
				</xsd:sequence>
			</xsd:complexType>
			<xsd:complexType name="directories">
				<xsd:complexContent>
					<xsd:restriction base="SOAP-ENC:Array">
						<attribute ref="SOAP-ENC:arrayType" wsdl:arrayType="tns:directory[]" />
					</xsd:restriction>
				</xsd:complexContent>
			</xsd:complexType>
			<xsd:complexType name="types">
				<xsd:complexContent>
					<xsd:restriction base="SOAP-ENC:Array">
							<attribute ref="SOAP-ENC:arrayType" wsdl:arrayType="xsd:string" />
					</xsd:restriction>
				</xsd:complexContent>
			</xsd:complexType>
		</xsd:schema>
	</types>

	<message name="getTypesRequest">
		<part name="username" type="xsd:string" />
		<part name="password" type="xsd:string" />
	</message>
	<message name="serviceGetTypesRequest">
		<part name="username" type="xsd:string" />
		<part name="serviceId" type="xsd:string" />
		<part name="serviceKey" type="xsd:string" />
	</message>
	<message name="getTypesResponse">
		<part name="types" type="types" />
	</message>
	<message name="getDirsRequest">
		<part name="username" type="xsd:string" />
		<part name="password" type="xsd:string" />
	</message>
	<message name="serviceGetDirsRequest">
		<part name="username" type="xsd:string" />
		<part name="serviceId" type="xsd:string" />
		<part name="serviceKey" type="xsd:string" />
	</message>
	<message name="getDirsResponse">
		<part name="directories" type="directories" />
	</message>
	<message name="getVideosRequest">
		<part name="username" type="xsd:string" />
		<part name="password" type="xsd:string" />
		<part name="directory" type="xsd:string" />
	</message>
	<message name="serviceGetVideosRequest">
		<part name="username" type="xsd:string" />
		<part name="serviceId" type="xsd:string" />
		<part name="serviceKey" type="xsd:string" />
		<part name="directory" type="xsd:string" />
	</message>
	<message name="getVideosResponse">
		<part name="videos" type="videos" />
	</message>
	<message name="getVideoRequest">
		<part name="username" type="xsd:string" />
		<part name="password" type="xsd:string" />
		<part name="directory" type="xsd:string" />
		<part name="file" type="xsd:string" />
	</message>
	<message name="serviceGetVideoRequest">
		<part name="username" type="xsd:string" />
		<part name="serviceId" type="xsd:string" />
		<part name="serviceKey" type="xsd:string" />
		<part name="directory" type="xsd:string" />
		<part name="file" type="xsd:string" />
	</message>
	<message name="getVideoResponse">
		<part name="video" type="video" />
	</message>
	<message name="addVideoRequest">
		<part name="username" type="xsd:string" />
		<part name="password" type="xsd:string" />
		<part name="directory" type="xsd:string" />
		<part name="file" type="xsd:base64Binary" />
		<part name="filename" type="xsd:string" />
		<part name="filetype" type="xsd:string" />
		<part name="filesize" type="xsd:integer" />
	</message>
	<message name="serviceAddVideoRequest">
		<part name="username" type="xsd:string" />
		<part name="serviceId" type="xsd:string" />
		<part name="serviceKey" type="xsd:string" />
		<part name="directory" type="xsd:string" />
		<part name="file" type="xsd:base64Binary" />
		<part name="filename" type="xsd:string" />
		<part name="filetype" type="xsd:string" />
		<part name="filesize" type="xsd:integer" />
	</message>
	<message name="addVideoResponse">
		<part name="video" type="video" />
	</message>
	<message name="delVideoRequest">
		<part name="username" type="xsd:string" />
		<part name="password" type="xsd:string" />
		<part name="directory" type="xsd:string" />
		<part name="file" type="xsd:string" />
	</message>
	<message name="serviceDelVideoRequest">
		<part name="username" type="xsd:string" />
		<part name="serviceId" type="xsd:string" />
		<part name="serviceKey" type="xsd:string" />
		<part name="directory" type="xsd:string" />
		<part name="file" type="xsd:string" />
	</message>
	<message name="delVideoResponse">
		<part name="result" type="xsd:boolean" />
	</message>
	<portType name="MiddTubePortType">
		<operation name="getTypes">
			<documentation>
				Get a list of allowed file type extensions.
			</documentation>
			<input message="tns:getTypesRequest" />
			<output message="tns:getTypesResponse" />
		</operation>
		<operation name="getDirs">
			<documentation>
				Get a list of directories for a user or group.
			</documentation>
			<input message="tns:getDirsRequest" />
			<output message="tns:getDirsResponse" />
		</operation>
		<operation name="getVideos">
			<documentation>
				Get a list of video information for a user or group.
			</documentation>
			<input message="tns:getVideosRequest" />
			<output message="tns:getVideosResponse" />
		</operation>
		<operation name="getVideo">
			<documentation>
				Get video information for a single video.
			</documentation>
			<input message="tns:getVideoRequest" />
			<output message="tns:getVideoResponse" />

		</operation>
		<operation name="addVideo">
			<documentation>
				Add a video file to a user or group's directory.
			</documentation>
			<input message="tns:addVideoRequest" />
			<output message="tns:addVideoResponse" />
		</operation>
		<operation name="delVideo">
			<documentation>
				Remove a video file from a user or group's directory.
			</documentation>
			<input message="tns:delVideoRequest" />
			<output message="tns:delVideoResponse" />
		</operation>
		<operation name="serviceGetTypes">
			<documentation>
				Get a list of allowed file type extensions.
			</documentation>
			<input message="tns:serviceGetTypesRequest" />
			<output message="tns:getTypesResponse" />
		</operation>
		<operation name="serviceGetDirs">
			<documentation>
				Get a list of directories for a user or group.
			</documentation>
			<input message="tns:serviceGetDirsRequest" />
			<output message="tns:getDirsResponse" />
		</operation>
		<operation name="serviceGetVideos">
			<documentation>
				Get a list of video information for a user or group.
			</documentation>
			<input message="tns:serviceGetVideosRequest" />
			<output message="tns:getVideosResponse" />
		</operation>
		<operation name="serviceGetVideo">
			<documentation>
				Get video information for a single video.
			</documentation>
			<input message="tns:serviceGetVideoRequest" />
			<output message="tns:getVideoResponse" />

		</operation>
		<operation name="serviceAddVideo">
			<documentation>
				Add a video file to a user or group's directory.
			</documentation>
			<input message="tns:serviceAddVideoRequest" />
			<output message="tns:addVideoResponse" />
		</operation>
		<operation name="serviceDelVideo">
			<documentation>
				Remove a video file from a user or group's directory.
			</documentation>
			<input message="tns:serviceDelVideoRequest" />
			<output message="tns:delVideoResponse" />
		</operation>
	</portType>
	<binding name="MiddTubeBinding" type="tns:MiddTubePortType">
		<soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http" />
		<operation name="getTypes">
			<soap:operation soapAction="urn:MiddTube#getTypes" style="rpc" />
			<input>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</input>
			<output>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</output>
		</operation>
		<operation name="getDirs">
			<soap:operation soapAction="urn:MiddTube#getDirs" style="rpc" />
			<input>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</input>
			<output>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</output>
		</operation>
		<operation name="getVideos">
			<soap:operation soapAction="urn:MiddTube#getVideos" style="rpc" />
			<input>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</input>
			<output>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</output>
		</operation>
		<operation name="getVideo">
			<soap:operation soapAction="urn:MiddTube#getVideo" style="rpc" />
			<input>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</input>
			<output>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</output>
		</operation>
		<operation name="addVideo">
			<soap:operation soapAction="urn:MiddTube#addVideo" style="rpc" />
			<input>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</input>
			<output>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</output>
		</operation>
		<operation name="delVideo">
			<soap:operation soapAction="urn:MiddTube#delVideo" style="rpc" />
			<input>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</input>
			<output>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</output>
		</operation>
		<operation name="serviceGetTypes">
			<soap:operation soapAction="urn:MiddTube#serviceGetTypes" style="rpc" />
			<input>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</input>
			<output>
				<soap:body use="encoded" namespace="urn:MiddTueb" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</output>
		</operation>
		<operation name="serviceGetDirs">
			<soap:operation soapAction="urn:MiddTube#serviceGetDirs" style="rpc" />
			<input>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</input>
			<output>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</output>
		</operation>
		<operation name="serviceGetVideos">
			<soap:operation soapAction="urn:MiddTube#serviceGetVideos" style="rpc" />
			<input>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</input>
			<output>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</output>
		</operation>
		<operation name="serviceGetVideo">
			<soap:operation soapAction="urn:MiddTube#serviceGetVideo" style="rpc" />
			<input>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</input>
			<output>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</output>
		</operation>
		<operation name="serviceAddVideo">
			<soap:operation soapAction="urn:MiddTube#serviceAddVideo" style="rpc" />
			<input>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</input>
			<output>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</output>
		</operation>
		<operation name="serviceDelVideo">
			<soap:operation soapAction="urn:MiddTube#serviceDelVideo" style="rpc" />
			<input>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</input>
			<output>
				<soap:body use="encoded" namespace="urn:MiddTube" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" />
			</output>
		</operation>
	</binding>
	<service name="MiddTube">
		<port name="MiddTubePort" binding="MiddTubeBinding">
			<soap:address location="<?php print MYPATH.'/soap.php'; ?>" />
		</port>
	</service>
</definitions>