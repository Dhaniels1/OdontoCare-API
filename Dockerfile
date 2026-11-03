FROM mcr.microsoft.com/dotnet/sdk:10.0 AS build
WORKDIR /src
COPY OdontoCare.Domain/OdontoCare.Domain.csproj OdontoCare.Domain/
COPY OdontoCare.Application/OdontoCare.Application.csproj OdontoCare.Application/
COPY OdontoCare.Infrastructure/OdontoCare.Infrastructure.csproj OdontoCare.Infrastructure/
COPY OdontoCare.API/OdontoCare.API.csproj OdontoCare.API/
RUN dotnet restore OdontoCare.API/OdontoCare.API.csproj
COPY OdontoCare.Domain/ OdontoCare.Domain/
COPY OdontoCare.Application/ OdontoCare.Application/
COPY OdontoCare.Infrastructure/ OdontoCare.Infrastructure/
COPY OdontoCare.API/ OdontoCare.API/
RUN dotnet publish OdontoCare.API/OdontoCare.API.csproj -c Release --no-restore -o /app/publish
FROM mcr.microsoft.com/dotnet/aspnet:10.0
WORKDIR /app
COPY --from=build /app/publish .
ENV ASPNETCORE_HTTP_PORTS=8080
USER $APP_UID
EXPOSE 8080
ENTRYPOINT ["dotnet", "OdontoCare.API.dll"]
